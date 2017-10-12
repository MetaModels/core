<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Filter\Setting;

/**
 * This is an abstract factory to query instances of attributes.
 *
 * Extend your own attribute factories from this class and register them when the create attribute factory event is
 * triggered.
 */
abstract class AbstractFilterSettingTypeFactory implements IFilterSettingTypeFactory
{
    /**
     * The type name.
     *
     * @var string
     */
    private $typeName;

    /**
     * The name of the attribute class of this type.
     *
     * @var string
     */
    private $typeClass;

    /**
     * The icon representing this filter setting type.
     *
     * @var string
     */
    private $typeIcon;

    /**
     * The maximum amount of children allowed.
     *
     * If null, unlimited.
     *
     * @var int|null
     */
    private $maxChildren;

    /**
     * List of valid attribute types that can be filtered with this filter.
     *
     * @var string[]
     */
    private $attributeTypes;

    /**
     * Cache lookup variable.
     *
     * @var bool
     */
    private $isNestedType;

    /**
     * Create a new instance.
     */
    protected function __construct()
    {
        // Nothing to do, aside from making the constructor protected.
    }

    /**
     * Set the type class.
     *
     * @param string $typeClass The name of the class.
     *
     * @return AbstractFilterSettingTypeFactory
     */
    protected function setTypeClass($typeClass)
    {
        $this->typeClass = $typeClass;

        return $this;
    }

    /**
     * Set the type name.
     *
     * @param string $typeName The type name.
     *
     * @return AbstractFilterSettingTypeFactory
     */
    protected function setTypeName($typeName)
    {
        $this->typeName = $typeName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * Set the type icon.
     *
     * @param string $typeIcon The type icon to use.
     *
     * @return AbstractFilterSettingTypeFactory
     */
    protected function setTypeIcon($typeIcon)
    {
        $this->typeIcon = $typeIcon;

        return $this;
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
    public function createInstance($information, $filterSettings)
    {
        return new $this->typeClass($filterSettings, $information);
    }

    /**
     * Check if the type allows children.
     *
     * @return bool
     */
    public function isNestedType()
    {
        if (!isset($this->isNestedType)) {
            $this->isNestedType = in_array(
                'MetaModels\Filter\Setting\IWithChildren',
                class_implements($this->typeClass, true)
            );
        }

        return $this->isNestedType;
    }

    /**
     * Set the maximum amount of allowed children (only valid when isNestedType() == true).
     *
     * @param int|null $maxChildren The amount of children.
     *
     * @return AbstractFilterSettingTypeFactory
     *
     * @throws \LogicException When the filter setting can not handle children (is not nested type).
     */
    protected function setMaxChildren($maxChildren)
    {
        if (!$this->isNestedType()) {
            throw new \LogicException('Filter setting ' . $this->typeClass . ' can not handle children.');
        }

        $this->maxChildren = $maxChildren;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxChildren()
    {
        return $this->isNestedType() ? $this->maxChildren : 0;
    }

    /**
     * Setup the allowance of attribute types to be added to this factory.
     *
     * This must be called before any calls to addKnownAttributeType() is allowed.
     *
     * You can pass as many parameters as you need.
     *
     * Either pass one parameter as array of string or pass 1 to n parameters as string.
     *
     * @param string|string[] $initialType1toN One or more attribute type names to be available initially (optional).
     *
     * @return AbstractFilterSettingTypeFactory
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function allowAttributeTypes($initialType1toN = null)
    {
        if (is_array($initialType1toN)) {
            $this->attributeTypes = $initialType1toN;
        } else {
            $this->attributeTypes = func_get_args();
        }

        return $this;
    }

    /**
     * Retrieve the list of known attribute types.
     *
     * @return string[] The list of attribute names or null if no attributes are allowed.
     */
    public function getKnownAttributeTypes()
    {
        return $this->attributeTypes;
    }

    /**
     * Retrieve the list of known attribute types.
     *
     * @param string $typeName The attribute type name.
     *
     * @return IFilterSettingTypeFactory
     *
     * @throws \LogicException When the filter setting can not handle attributes.
     */
    public function addKnownAttributeType($typeName)
    {
        if (!is_array($this->attributeTypes)) {
            throw new \LogicException('Filter setting ' . $this->typeClass . ' can not handle attributes.');
        }

        $this->attributeTypes[$typeName] = $typeName;

        return $this;
    }
}
