<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Events;

use Contao\System;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\Events\CollectMetaModelAttributeInformationEvent;
use MetaModels\Helper\LocaleUtil;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\ITranslatedMetaModel;
use MetaModels\MetaModel;
use MetaModels\MetaModelsServiceContainer;
use MetaModels\TranslatedMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the information retriever database backend.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DatabaseBackedListener
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private Connection $database;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * All MetaModel instances created via this listener.
     *
     * Association: id => object
     *
     * @var IMetaModel[]
     */
    private array $instancesById = [];

    /**
     * All MetaModel instances.
     *
     * Association: tableName => object
     *
     * @var IMetaModel[]
     */
    private array $instancesByTable = [];

    /**
     * The table names.
     *
     * @var string[]
     */
    private array $tableNames = [];

    /**
     * Flag if the table names have already been collected.
     *
     * @var bool
     */
    private bool $tableNamesCollected = false;

    /**
     * All attribute information.
     *
     * @var array[]
     */
    private array $attributeInformation = [];

    /**
     * The system columns of MetaModels.
     *
     * @var string[]
     */
    private array $systemColumns;

    /**
     * Create a new instance.
     *
     * @param Connection               $database      The database connection.
     * @param EventDispatcherInterface $dispatcher    The event dispatcher.
     * @param string[]                 $systemColumns The system columns.
     */
    public function __construct(Connection $database, EventDispatcherInterface $dispatcher, array $systemColumns)
    {
        $this->database      = $database;
        $this->dispatcher    = $dispatcher;
        $this->systemColumns = $systemColumns;
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @deprecated The service container is deprecated and should not be used anymore.
     *
     * @psalm-suppress DeprecatedInterface
     */
    public function getServiceContainer()
    {
        /** @psalm-suppress DeprecatedClass */
        $serviceContainer = System::getContainer()->get(MetaModelsServiceContainer::class);
        assert($serviceContainer instanceof IMetaModelsServiceContainer);

        return $serviceContainer;
    }

    /**
     * Translate the id of a MetaModel to the correct name of the MetaModel.
     *
     * @param GetMetaModelNameFromIdEvent $event The event.
     *
     * @return void
     */
    public function getMetaModelNameFromId(GetMetaModelNameFromIdEvent $event)
    {
        $metaModelId = $event->getMetaModelId();
        if (\array_key_exists($metaModelId, $this->instancesById)) {
            $event->setMetaModelName($this->instancesById[$metaModelId]->getTableName());

            return;
        }

        if (isset($this->tableNames[$metaModelId])) {
            $event->setMetaModelName($this->tableNames[$metaModelId]);

            return;
        }

        if (!$this->tableNamesCollected) {
            $table = $this
                ->database
                ->createQueryBuilder()
                ->select('*')
                ->from('tl_metamodel', 't')
                ->where('t.id=:id')
                ->setParameter('id', $metaModelId)
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchAssociative();

            if (false !== $table) {
                $this->tableNames[$metaModelId] = $table['tableName'];
                $event->setMetaModelName($this->tableNames[$metaModelId]);
            }
        }
    }

    /**
     * Determines the correct factory from a metamodel table name and creates an instance using the factory.
     *
     * @param CreateMetaModelEvent $event   The event.
     * @param array                $arrData The meta information for the MetaModel.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function createInstanceViaLegacyFactory(CreateMetaModelEvent $event, $arrData)
    {
        $name = $arrData['tableName'];
        if (!isset($GLOBALS['METAMODELS']['factories'][$name])) {
            return false;
        }

        // @codingStandardsIgnoreStart
        @trigger_error('Creating MetaModel instances via global factories is deprecated.', E_USER_DEPRECATED);
        // @codingStandardsIgnoreEnd

        $factoryClass = $GLOBALS['METAMODELS']['factories'][$name];
        $event->setMetaModel(\call_user_func_array([$factoryClass, 'createInstance'], [$arrData]));

        return (bool) $event->getMetaModel();
    }

    /**
     * Create a MetaModel instance with the given information.
     *
     * @param CreateMetaModelEvent $event   The event.
     * @param array                $arrData The meta information for the MetaModel.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function createInstance(CreateMetaModelEvent $event, $arrData)
    {
        if (false === $this->createInstanceViaLegacyFactory($event, $arrData)) {
            if ($arrData['translated']) {
                $metaModel = new TranslatedMetaModel($arrData, $this->dispatcher, $this->database);
                // @deprecated usage of TL_LANGUAGE - remove for Contao 5.0.
                $metaModel->selectLanguage(LocaleUtil::formatAsLocale($GLOBALS['TL_LANGUAGE'] ?? 'en'));
            } else {
                $metaModel = new MetaModel($arrData, $this->dispatcher, $this->database);
            }

            /** @psalm-suppress DeprecatedMethod */
            $metaModel->setServiceContainer(function () {
                /** @psalm-suppress DeprecatedMethod */
                return $this->getServiceContainer();
            }, false);
            $event->setMetaModel($metaModel);
        }

        if ($metaModel = $event->getMetaModel()) {
            $this->instancesByTable[$event->getMetaModelName()] = $metaModel;
            $this->instancesById[$metaModel->get('id')]         = $metaModel;
        }
    }

    /**
     * Create a MetaModel instance.
     *
     * @param CreateMetaModelEvent $event The event.
     *
     * @return void
     */
    public function createMetaModel(CreateMetaModelEvent $event)
    {
        if ($event->getMetaModel() !== null) {
            /** @psalm-suppress DeprecatedMethod */
            if (!($metaModel = $event->getMetaModel()) instanceof ITranslatedMetaModel && $metaModel->isTranslated()) {
                // @codingStandardsIgnoreStart
                @\trigger_error(
                    'Translated "\MetaModel\IMetamodel" instances are deprecated since MetaModels 2.2 ' .
                    'and to be removed in 3.0. The MetaModel must implement "\MetaModels\ITranslatedMetaModel".',
                    E_USER_DEPRECATED
                );
                // @codingStandardsIgnoreEnd
            }
            return;
        }

        if (isset($this->instancesByTable[$metaModelName = $event->getMetaModelName()])) {
            $event->setMetaModel($this->instancesByTable[$metaModelName]);

            return;
        }

        $table = $this
            ->database
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel', 't')
            ->where('t.tableName=:tableName')
            ->setParameter('tableName', $metaModelName)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        if (false !== $table) {
            $table['system_columns'] = $this->systemColumns;

            $this->createInstance($event, $table);
        }
    }

    /**
     * Collect the table names from the database.
     *
     * @param CollectMetaModelTableNamesEvent $event The event.
     *
     * @return void
     */
    public function collectMetaModelTableNames(CollectMetaModelTableNamesEvent $event)
    {
        if ($this->tableNamesCollected) {
            $event->addMetaModelNames($this->tableNames);

            return;
        }

        $tables = $this
            ->database
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel', 't')
            ->orderBy('t.sorting')
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($tables as $table) {
            $this->tableNames[$table['id']] = $table['tableName'];
        }

        $event->addMetaModelNames($this->tableNames);
        $this->tableNamesCollected = true;
    }

    /**
     * Collect all attribute information from the database for the MetaModel.
     *
     * @param CollectMetaModelAttributeInformationEvent $event The event.
     *
     * @return void
     */
    public function collectMetaModelAttributeInformation(CollectMetaModelAttributeInformationEvent $event)
    {
        $metaModelName = $event->getMetaModel()->getTableName();
        if (!\array_key_exists($metaModelName, $this->attributeInformation)) {
            $attributes = $this
                ->database
                ->createQueryBuilder()
                ->select('*')
                ->from('tl_metamodel_attribute', 't')
                ->where('t.pid=:pid')
                ->setParameter('pid', $event->getMetaModel()->get('id'))
                ->orderBy('t.sorting')
                ->executeQuery()
                ->fetchAllAssociative();

            $this->attributeInformation[$metaModelName] = [];
            foreach ($attributes as $attribute) {
                $colName = $attribute['colname'];

                $this->attributeInformation[$metaModelName][$colName] = $attribute;
            }
        }

        foreach ($this->attributeInformation[$metaModelName] as $name => $information) {
            $event->addAttributeInformation($name, $information);
        }
    }
}
