<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Attribute;

/**
 * This is an abstract factory to query instances of attributes.
 *
 * Extend your own attribute factories from this class and register them when the create attribute factory event is
 * triggered.
 */
abstract class AbstractAttributeTypeFactory implements IAttributeTypeFactory
{
    /**
     * The type name.
     *
     * @var string
     */
    protected $typeName = '';

    /**
     * The name of the attribute class of this type.
     *
     * @var class-string<IAttribute>
     */
    protected $typeClass = IAttribute::class;

    /**
     * The icon representing this attribute type.
     *
     * @var string
     */
    protected $typeIcon = '';

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
        return \in_array('MetaModels\Attribute\ITranslated', \class_implements($this->typeClass), true);
    }

    /**
     * Check if the type is of simple nature.
     *
     * @return bool
     */
    public function isSimpleType()
    {
        return \in_array('MetaModels\Attribute\ISimple', \class_implements($this->typeClass), true);
    }

    /**
     * Check if the type is of complex nature.
     *
     * @return bool
     */
    public function isComplexType()
    {
        return \in_array('MetaModels\Attribute\IComplex', \class_implements($this->typeClass), true);
    }
}
