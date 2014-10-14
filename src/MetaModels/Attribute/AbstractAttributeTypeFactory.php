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

namespace MetaModels\Attribute;

/**
 * This is an abstract factory to query instances of attributes.
 *
 * Extend your own attribute factories from this class and register them when the create attribute factory event is
 * triggered.
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
abstract class AbstractAttributeTypeFactory implements IAttributeTypeFactory
{
    /**
     * The type name.
     *
     * @var string
     */
    protected $typeName;

    /**
     * The name of the attribute class of this type.
     *
     * @var string
     */
    protected $typeClass;

    /**
     * The icon representing this attributy type.
     *
     * @var string
     */
    protected $typeIcon;

    /**
     * {@inheritdoc}
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeIcon()
    {
        return $this->typeIcon;
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($information, $metaModel)
    {
        return new $this->typeClass($metaModel, $information);
    }

    /**
     * Create a new instance.
     */
    protected function __construct()
    {
        // Nothing to do, aside from making the constructor protected.
    }

    /**
     * Check if the type is translated.
     *
     * @return bool
     */
    public function isTranslatedType()
    {
        return in_array('MetaModels\Attribute\ITranslated', class_implements($this->typeClass, true));
    }

    /**
     * Check if the type is of simple nature.
     *
     * @return bool
     */
    public function isSimpleType()
    {
        return in_array('MetaModels\Attribute\ISimple', class_implements($this->typeClass, true));
    }

    /**
     * Check if the type is of complex nature.
     *
     * @return bool
     */
    public function isComplexType()
    {
        return in_array('MetaModels\Attribute\IComplex', class_implements($this->typeClass, true));
    }
}
