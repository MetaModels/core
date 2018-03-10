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
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Events;

use MetaModels\Attribute\Events\CollectMetaModelAttributeInformationEvent;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModel;

/**
 * This is the MetaModel factory interface.
 *
 * To create a MetaModel instance, either call @link{MetaModelFactory::byId()} or @link{MetaModelFactory::byTableName()}
 */
class DatabaseBackedListener
{
    /**
     * All MetaModel instances created via this listener.
     *
     * Association: id => object
     *
     * @var IMetaModel[]
     */
    protected static $instancesById = array();

    /**
     * All MetaModel instances.
     *
     * Association: tableName => object
     *
     * @var IMetaModel[]
     */
    protected static $instancesByTable = array();

    /**
     * The table names.
     *
     * @var string[]
     */
    protected static $tableNames = null;

    /**
     * Flag if the table names have already been collected.
     *
     * @var bool
     */
    protected static $tableNamesCollected = false;

    /**
     * All attribute information.
     *
     * @var array[]
     */
    protected static $attributeInformation = array();

    /**
     * The service container.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $serviceContainer;

    /**
     * Register to the event dispatcher in the provided service container.
     *
     * @param MetaModelsBootEvent $event The event.
     *
     * @return void
     */
    public function handleEvent(MetaModelsBootEvent $event)
    {
        $this->serviceContainer = $event->getServiceContainer();

        $dispatcher = $this->getServiceContainer()->getEventDispatcher();

        $dispatcher->addListener(
            CollectMetaModelAttributeInformationEvent::NAME,
            array($this, 'collectMetaModelAttributeInformation')
        );
        $dispatcher->addListener(
            CollectMetaModelTableNamesEvent::NAME,
            array($this, 'collectMetaModelTableNames')
        );
        $dispatcher->addListener(
            CreateMetaModelEvent::NAME,
            array($this, 'createMetaModel')
        );
        $dispatcher->addListener(
            GetMetaModelNameFromIdEvent::NAME,
            array($this, 'getMetaModelNameFromId')
        );
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * Retrieve the system database.
     *
     * @return \Contao\Database
     */
    protected function getDatabase()
    {
        return $this->getServiceContainer()->getDatabase();
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
        if (array_key_exists($event->getMetaModelId(), self::$instancesById)) {
            $event->setMetaModelName(self::$instancesById[$event->getMetaModelId()]->getTableName());

            return;
        }

        if (isset(self::$tableNames[$event->getMetaModelId()])) {
            $event->setMetaModelName(self::$tableNames[$event->getMetaModelId()]);

            return;
        }

        if (!self::$tableNamesCollected) {
            $objData = $this->getDatabase()->prepare('SELECT * FROM tl_metamodel WHERE id=?')
                ->limit(1)
                ->execute($event->getMetaModelId());
            /** @noinspection PhpUndefinedFieldInspection */
            if ($objData->numRows) {
                /** @noinspection PhpUndefinedFieldInspection */
                self::$tableNames[$event->getMetaModelId()] = $objData->tableName;
                $event->setMetaModelName(self::$tableNames[$event->getMetaModelId()]);
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
            $metaModel = new MetaModel($arrData);
            $metaModel->setServiceContainer($this->getServiceContainer());
            $event->setMetaModel($metaModel);
        }

        if ($event->getMetaModel()) {
            self::$instancesByTable[$event->getMetaModelName()]     = $event->getMetaModel();
            self::$instancesById[$event->getMetaModel()->get('id')] = $event->getMetaModel();
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

        if (isset(self::$instancesByTable[$event->getMetaModelName()])) {
            $event->setMetaModel(self::$instancesByTable[$event->getMetaModelName()]);

            return;
        }

        $objData = $this->getDatabase()->prepare('SELECT * FROM tl_metamodel WHERE tableName=?')
            ->limit(1)
            ->execute($event->getMetaModelName());

        /** @noinspection PhpUndefinedFieldInspection */
        if ($objData->numRows) {
            $this->createInstance($event, $objData->row());
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
        if (self::$tableNamesCollected) {
            $event->addMetaModelNames(self::$tableNames);

            return;
        }

        $objDB = $this->getDatabase();
        if (!$objDB->tableExists('tl_metamodel')) {
            // I can't work without a properly installed database.
            return;
        }

        self::$tableNames = $objDB->execute('SELECT * FROM tl_metamodel order by sorting')
            ->fetchEach('tableName');

        $event->addMetaModelNames(self::$tableNames);
        self::$tableNamesCollected = true;
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
        if (isset(self::$attributeInformation[$event->getMetaModel()->getTableName()])) {
            foreach (self::$attributeInformation[$event->getMetaModel()->getTableName()] as $name => $information) {
                $event->addAttributeInformation($name, $information);
            }

            return;
        }

        $database   = $this->getDatabase();
        $attributes = $database->prepare('SELECT * FROM tl_metamodel_attribute WHERE pid=?')
            ->execute($event->getMetaModel()->get('id'));

        $metaModelName = $event->getMetaModel()->getTableName();
        while ($attributes->next()) {
            /** @noinspection PhpUndefinedFieldInspection */
            $colName = $attributes->colname;

            self::$attributeInformation[$metaModelName][$colName] = $attributes->row();
            $event->addAttributeInformation($colName, $attributes->row());
        }
    }
}
