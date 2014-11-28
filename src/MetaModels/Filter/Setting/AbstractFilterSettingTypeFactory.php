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

namespace MetaModels\Filter\Setting;

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
abstract class AbstractFilterSettingTypeFactory implements IFilterSettingTypeFactory
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
     * The icon representing this filter setting type.
     *
     * @var string
     */
    protected $typeIcon;

    /**
     * The maximum amount of children allowed.
     *
     * If null, unlimited.
     *
     * @var int|null
     */
    protected $maxChildren;

    /**
     * Create a new instance.
     */
    protected function __construct()
    {
        // Nothing to do, aside from making the constructor protected.
    }

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
     * Check if the type allows children.
     *
     * @return bool
     */
    public function isNestedType()
    {
        return in_array('MetaModels\Filter\Setting\IWithChildren', class_implements($this->typeClass, true));
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxChildren()
    {
        return $this->maxChildren;
    }
}
