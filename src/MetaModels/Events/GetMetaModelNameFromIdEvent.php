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

use Symfony\Component\EventDispatcher\Event;

/**
 * This event is triggered when a MetaModel id must get translated to a MetaModel name.
 */
class GetMetaModelNameFromIdEvent extends Event
{
    /**
     * The event name.
     */
    const NAME = 'metamodels.metamodel.get-metamodel-name-from-id';

    /**
     * The MetaModel id to be translated.
     *
     * @var string
     */
    protected $metaModelId;

    /**
     * The name of the MetaModel.
     *
     * @var string
     */
    protected $metaModelName;

    /**
     * Create a new instance.
     *
     * @param string $metaModelId The id to be translated.
     */
    public function __construct($metaModelId)
    {
        $this->metaModelId = $metaModelId;
    }

    /**
     * Retrieve the MetaModel id.
     *
     * @return string
     */
    public function getMetaModelId()
    {
        return $this->metaModelId;
    }

    /**
     * Retrieve the MetaModel name.
     *
     * @return string
     */
    public function getMetaModelName()
    {
        return $this->metaModelName;
    }

    /**
     * Set the MetaModel name.
     *
     * @param string $metaModelName The name of the MetaModel.
     *
     * @return GetMetaModelNameFromIdEvent
     */
    public function setMetaModelName($metaModelName)
    {
        $this->metaModelName = $metaModelName;

        return $this;
    }
}
