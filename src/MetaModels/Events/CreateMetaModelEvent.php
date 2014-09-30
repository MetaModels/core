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

use MetaModels\IFactory;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is triggered for every metamodel when a factory wants to create an instance.
 */
class CreateMetaModelEvent extends Event
{
    /**
     * The event name.
     */
    const NAME = 'metamodels.metamodel.create';

    /**
     * The factory calling the event.
     *
     * @var IFactory
     */
    protected $factory;

    /**
     * The name of the MetaModel to be created.
     *
     * @var string
     */
    protected $metaModelName;

    /**
     * The MetaModel instance being created.
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * Create a new instance.
     *
     * @param IFactory $factory       The MetaModel factory dispatching this event.
     *
     * @param string   $metaModelName The name of the MetaModel to be created.
     */
    public function __construct($factory, $metaModelName)
    {
        $this->factory       = $factory;
        $this->metaModelName = $metaModelName;
    }

    /**
     * Retrieve the factory.
     *
     * @return IFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Retrieve the MetaModel name to be created.
     *
     * @return string
     */
    public function getMetaModelName()
    {
        return $this->metaModelName;
    }

    /**
     * Retrieve the MetaModel instance.
     *
     * @return IMetaModel
     */
    public function getMetaModel()
    {
        return $this->metaModel;
    }

    /**
     * Set the MetaModel instance.
     *
     * @param IMetaModel $metaModel The MetaModel instance.
     *
     * @return CreateMetaModelEvent
     */
    public function setMetaModel($metaModel)
    {
        $this->metaModel = $metaModel;

        return $this;
    }
}
