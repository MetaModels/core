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

namespace MetaModels\Attribute\Events;

use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is triggered for every metamodel when the attribute information for the MetaModel shall be retrieved.
 */
class CollectMetaModelAttributeInformationEvent extends Event
{
    /**
     * The event name.
     */
    const NAME = 'metamodels.metamodel.collect-metamodel-attribute-information';

    /**
     * The MetaModel instance being created.
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * The attribute information.
     *
     * @var array[]
     */
    protected $attributeInformation = array();

    /**
     * Create a new instance.
     *
     * @param IMetaModel $metaModel The name of the MetaModel to collect the attribute information for.
     */
    public function __construct(IMetaModel $metaModel)
    {
        $this->metaModel = $metaModel;
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
     * Retrieve the attribute information.
     *
     * @return array
     */
    public function getAttributeInformation()
    {
        return $this->attributeInformation;
    }

    /**
     * Set the attribute information.
     *
     * @param array[] $attributeInformation The attribute information.
     *
     * @return CollectMetaModelAttributeInformationEvent
     */
    public function setAttributeInformation($attributeInformation)
    {
        $this->attributeInformation = $attributeInformation;

        return $this;
    }

    /**
     * Set the MetaModel instance.
     *
     * @param string $attributeName        The internal name of the attribute (column name).
     *
     * @param array  $attributeInformation The attribute information.
     *
     * @return CollectMetaModelAttributeInformationEvent
     */
    public function addAttributeInformation($attributeName, $attributeInformation)
    {
        $this->attributeInformation[$attributeName] = $attributeInformation;

        return $this;
    }
}
