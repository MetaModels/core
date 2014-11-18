<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Events;

use MetaModels\Attribute\Events\CollectMetaModelAttributeInformationEvent;
use MetaModels\IMetaModel;
use MetaModels\MetaModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This is the MetaModel factory interface.
 *
 * To create a MetaModel instance, either call @link{MetaModelFactory::byId()} or @link{MetaModelFactory::byTableName()}
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
// FIXME: Class must be turned into event listeners registered with service container present.
class DatabaseBackedListener implements EventSubscriberInterface
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
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array
        (
            CollectMetaModelAttributeInformationEvent::NAME => 'collectMetaModelAttributeInformation',
            CollectMetaModelTableNamesEvent::NAME           => 'collectMetaModelTableNames',
            CreateMetaModelEvent::NAME                      => 'createMetaModel',
            GetMetaModelNameFromIdEvent::NAME               => 'getMetaModelNameFromId',
        );
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
            $objData = \Database::getInstance()->prepare('SELECT * FROM tl_metamodel WHERE id=?')
                ->limit(1)
                ->execute($event->getMetaModelId());
            if ($objData->numRows) {
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
            $metaModel->setDatabase(\Database::getInstance());
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

        $objData = \Database::getInstance()->prepare('SELECT * FROM tl_metamodel WHERE tableName=?')
            ->limit(1)
            ->execute($event->getMetaModelName());

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

        $objDB = \Database::getInstance();
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

        $database   = \Database::getInstance();
        $attributes = $database->prepare('SELECT * FROM tl_metamodel_attribute WHERE pid=?')
            ->execute($event->getMetaModel()->get('id'));

        $metaModelName = $event->getMetaModel()->getTableName();
        while ($attributes->next()) {
            self::$attributeInformation[$metaModelName][$attributes->colname] = $attributes->row();
            $event->addAttributeInformation($attributes->colname, $attributes->row());
        }
    }
}
