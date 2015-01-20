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
 * This event is triggered to collect the names of all known MetaModels.
 */
class CollectMetaModelTableNamesEvent extends Event
{
    /**
     * The event name.
     */
    const NAME = 'metamodels.metamodel.collect-table-names';

    /**
     * The factory calling the event.
     *
     * @var IFactory
     */
    protected $factory;

    /**
     * The names of the MetaModels.
     *
     * @var string[]
     */
    protected $metaModelNames = array();

    /**
     * Create a new instance.
     *
     * @param IFactory $factory The MetaModel factory dispatching this event.
     */
    public function __construct($factory)
    {
        $this->factory = $factory;
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
     * @return string[]
     */
    public function getMetaModelNames()
    {
        return $this->metaModelNames;
    }

    /**
     * Set the MetaModel instance.
     *
     * @param IMetaModel $metaModelNames The MetaModel names.
     *
     * @return CollectMetaModelTableNamesEvent
     */
    public function setMetaModelNames($metaModelNames)
    {
        $this->metaModelNames = $metaModelNames;

        return $this;
    }

    /**
     * Set the MetaModel instance.
     *
     * @param string[] $metaModelNames The MetaModel names.
     *
     * @return CollectMetaModelTableNamesEvent
     */
    public function addMetaModelNames($metaModelNames)
    {
        $this->metaModelNames = array_unique(array_merge($this->metaModelNames, $metaModelNames));

        return $this;
    }
}
