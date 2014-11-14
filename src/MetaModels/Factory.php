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

namespace MetaModels;

use MetaModels\Events\CollectMetaModelTableNamesEvent;
use MetaModels\Events\CreateMetaModelEvent;
use MetaModels\Events\GetMetaModelNameFromIdEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the MetaModel factory interface.
 *
 * To create a MetaModel instance, either call @link{MetaModelFactory::byId()} or @link{MetaModelFactory::byTableName()}
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Factory implements IFactory
{
    /**
     * The event dispatcher.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $serviceContainer;

    /**
     * Create a new instance.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The service container to use.
     */
    public function __construct(IMetaModelsServiceContainer $serviceContainer)
    {
        $this->setServiceContainer($serviceContainer);
    }

    /**
     * Set the event dispatcher.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The service container to use.
     *
     * @return Factory
     */
    public function setServiceContainer(IMetaModelsServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;

        return $this;
    }

    /**
     * Retrieve the event dispatcher.
     *
     * @return IMetaModelsServiceContainer
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->getServiceContainer()->getEventDispatcher();
    }

    /**
     * {@inheritdoc}
     */
    public function translateIdToMetaModelName($metaModelId)
    {
        $event = new GetMetaModelNameFromIdEvent($metaModelId);

        $this->getEventDispatcher()->dispatch($event::NAME, $event);

        return $event->getMetaModelName();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaModel($metaModelName)
    {
        $event = new CreateMetaModelEvent($this, $metaModelName);

        $this->getEventDispatcher()->dispatch($event::NAME, $event);

        $metaModel = $event->getMetaModel();

        if ($metaModel) {
            $attributeFactory = $this->getServiceContainer()->getAttributeFactory();
            foreach ($attributeFactory->createAttributesForMetaModel($metaModel) as $attribute) {
                $metaModel->addAttribute($attribute);
            }
        }

        return $metaModel;
    }

    /**
     * {@inheritdoc}
     */
    public function collectNames()
    {
        $event = new CollectMetaModelTableNamesEvent($this);

        $this->getEventDispatcher()->dispatch($event::NAME, $event);

        return $event->getMetaModelNames();
    }

    /**
     * Retrieve the default factory from the default container.
     *
     * @return IFactory
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getDefaultFactory()
    {
        /** @var IMetaModelsServiceContainer $serviceContainer */
        $serviceContainer = $GLOBALS['container']['metamodels-service-container'];

        return $serviceContainer->getFactory();
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated To create an instance use methods translateIdToMetaModelName() and createMetaModel().
     */
    public static function byId($intId)
    {
        $factory = static::getDefaultFactory();
        $name    = $factory->translateIdToMetaModelName($intId);

        return $factory->getMetaModel($name);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated To create an instance use method createMetaModel().
     */
    public static function byTableName($strTableName)
    {
        $factory = static::getDefaultFactory();

        return $factory->getMetaModel($strTableName);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated To retrieve all names use method collectNames().
     */
    public static function getAllTables()
    {
        $factory = static::getDefaultFactory();

        return $factory->collectNames();
    }
}
