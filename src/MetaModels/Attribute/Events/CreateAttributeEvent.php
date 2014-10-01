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

use MetaModels\Attribute\IAttribute;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is triggered for every attribute when the factory wants to create an instance.
 */
class CreateAttributeEvent extends Event
{
    /**
     * The event name.
     */
    const NAME = 'metamodels.attribute.create';

    /**
     * The attribute information.
     *
     * @var array
     */
    protected $attributeInformation;

    /**
     * The MetaModel instance this attribute shall be created for.
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * The attribute instance.
     *
     * @var IAttribute
     */
    protected $attribute;

    /**
     * Create a new instance.
     *
     * @param array      $attributeInformation The attribute information array.
     *
     * @param IMetaModel $metaModel            The MetaModel instance for which the attribute shall get created for.
     */
    public function __construct($attributeInformation, $metaModel)
    {
        $this->attributeInformation = $attributeInformation;
        $this->metaModel            = $metaModel;
    }

    /**
     * Retrieve the attribute information array.
     *
     * @return array
     */
    public function getAttributeInformation()
    {
        return $this->attributeInformation;
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
     * Retrieve the attribute instance.
     *
     * @return IAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set the attribute instance.
     *
     * @param IAttribute $attribute The instance to store.
     *
     * @return CreateAttributeEvent
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }
}
