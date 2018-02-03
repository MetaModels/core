<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Events;

use Contao\System;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\Events\CollectMetaModelAttributeInformationEvent;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModel;
use MetaModels\MetaModelsServiceContainer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the information retriever database backend.
 */
class DatabaseBackedListener
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $database;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * All MetaModel instances created via this listener.
     *
     * Association: id => object
     *
     * @var IMetaModel[]
     */
    private $instancesById = [];

    /**
     * All MetaModel instances.
     *
     * Association: tableName => object
     *
     * @var IMetaModel[]
     */
    private $instancesByTable = [];

    /**
     * The table names.
     *
     * @var string[]
     */
    private $tableNames = [];

    /**
     * Flag if the table names have already been collected.
     *
     * @var bool
     */
    private $tableNamesCollected = false;

    /**
     * All attribute information.
     *
     * @var array[]
     */
    private $attributeInformation = [];

    /**
     * Create a new instance.
     *
     * @param Connection               $database   The database connection.
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     */
    public function __construct(Connection $database, EventDispatcherInterface $dispatcher)
    {
        $this->database   = $database;
        $this->dispatcher = $dispatcher;
    }
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @deprecated The service container is deprecated and should not be used anymore.
     */
    public function getServiceContainer()
    {
        return System::getContainer()->get(MetaModelsServiceContainer::class);
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
        $metaModelId =$event->getMetaModelId();
        if (array_key_exists($metaModelId, $this->instancesById)) {
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
                ->from('tl_metamodel')
                ->where('id=:id')
                ->setParameter('id', $metaModelId)
                ->setMaxResults(1)
                ->execute()
                ->fetch(\PDO::FETCH_ASSOC);

            if ($table) {
                $this->tableNames[$metaModelId] = $table['tableName'];
                $event->setMetaModelName($this->tableNames[$metaModelId]);
            }
        }
    }

    /**
     * Determines the correct factory from a metamodel table name and creates an instance using the factory.
     *
     * @param CreateMetaModelEvent $event   The event.
     *
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

        @trigger_error('Creating MetaModel instances via global factories is deprecated.', E_USER_DEPRECATED);

        $factoryClass = $GLOBALS['METAMODELS']['factories'][$name];
        $event->setMetaModel(call_user_func_array(array($factoryClass, 'createInstance'), array($arrData)));

        return $event->getMetaModel() !== null;
    }

    /**
     * Create a MetaModel instance with the given information.
     *
     * @param CreateMetaModelEvent $event   The event.
     *
     * @param array                $arrData The meta information for the MetaModel.
     *
     * @return void
     */
    protected function createInstance(CreateMetaModelEvent $event, $arrData)
    {
        if (!$this->createInstanceViaLegacyFactory($event, $arrData)) {
            $metaModel = new MetaModel($arrData, $this->dispatcher, $this->database);
            $metaModel->setServiceContainer(function () {
                return $this->getServiceContainer();
            }, false);
            $event->setMetaModel($metaModel);
        }

        if ($event->getMetaModel()) {
            $this->instancesByTable[$event->getMetaModelName()]     = $event->getMetaModel();
            $this->instancesById[$event->getMetaModel()->get('id')] = $event->getMetaModel();
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
            return;
        }

        if (isset($this->instancesByTable[$event->getMetaModelName()])) {
            $event->setMetaModel($this->instancesByTable[$event->getMetaModelName()]);

            return;
        }

        $table = $this
            ->database
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel')
            ->where('tableName=:tableName')
            ->setParameter('tableName', $event->getMetaModelName())
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if ($table) {
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
            ->from('tl_metamodel')
            ->orderBy('sorting')
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

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
        if (!array_key_exists($metaModelName, $this->attributeInformation)) {
            $attributes = $this
                ->database
                ->createQueryBuilder()
                ->select('*')
                ->from('tl_metamodel_attribute')
                ->where('pid=:pid')
                ->setParameter('pid', $event->getMetaModel()->get('id'))
                ->orderBy('sorting')
                ->execute()
                ->fetchAll(\PDO::FETCH_ASSOC);

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
