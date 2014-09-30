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
use MetaModels\Attribute\IFactory as IAttributeFactory;
use MetaModels\Attribute\Factory as AttributeFactory;
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
     * The default factory instance.
     *
     * @var IFactory
     */
    protected static $defaultFactory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * The attribute factory to use.
     *
     * @var IAttributeFactory
     */
    protected $attributeFactory;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $eventDispatcher  The event dispatcher to use.
     *
     * @param IAttributeFactory        $attributeFactory The attribute factory to use.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, IAttributeFactory $attributeFactory)
    {
        $this->setEventDispatcher($eventDispatcher);

        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Set the event dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher to set.
     *
     * @return Factory
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * Retrieve the attribute factory.
     *
     * @return IAttributeFactory
     */
    public function getAttributeFactory()
    {
        return $this->attributeFactory;
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
            $attributeFactory = $this->getAttributeFactory();
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
     * Inline create an instance of this factory.
     *
     * @return IFactory
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private static function createDefaultFactory()
    {
        $eventDispatcher  = $GLOBALS['container']['event-dispatcher'];
        $attributeFactory = new AttributeFactory($eventDispatcher);
        return new static($eventDispatcher, $attributeFactory);
    }

    /**
     * Inline create an instance of this factory.
     *
     * @return IFactory
     */
    public static function getDefaultFactory()
    {
        if (!self::$defaultFactory) {
            self::$defaultFactory = self::createDefaultFactory();
        }

        return self::$defaultFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function byId($intId)
    {
        $factory = static::getDefaultFactory();
        $name    = $factory->translateIdToMetaModelName($intId);

        return $factory->getMetaModel($name);
    }

    /**
     * {@inheritdoc}
     */
    public static function byTableName($strTableName)
    {
        $factory = static::getDefaultFactory();

        return $factory->getMetaModel($strTableName);
    }

    /**
     * {@inheritdoc}
     */
    public static function getAllTables()
    {
        $factory = static::getDefaultFactory();

        return $factory->collectNames();
    }
}
