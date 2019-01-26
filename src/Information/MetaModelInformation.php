<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\Information;

/**
 * This holds the schema information for a MetaModel.
 */
class MetaModelInformation implements MetaModelInformationInterface
{
    use ConfigurationTrait;

    /**
     * The name of the attribute.
     *
     * @var string
     */
    private $name;

    /**
     * The array of attribute information.
     *
     * @var AttributeInformationInterface[]
     */
    private $attributes = [];

    /**
     * Create a new instance.
     *
     * @param string $name          The name of the metamodel.
     * @param array  $configuration The initial configuration.
     */
    public function __construct(string $name, array $configuration = [])
    {
        $this->name          = $name;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeNames(): array
    {
        return array_keys($this->attributes);
    }

    /**
     * Add schema information for an attribute.
     *
     * @param AttributeInformationInterface $attribute The attribute schema information.
     *
     * @return void
     *
     * @throws \InvalidArgumentException When the MetaModel has already been registered.
     */
    public function addAttribute(AttributeInformationInterface $attribute): void
    {
        if ($this->hasAttribute($name = $attribute->getName())) {
            throw new \InvalidArgumentException('Attribute "' . $name . '" already registered');
        }

        $this->attributes[$name] = $attribute;
    }

    /**
     * {@inheritDoc}
     *
     * @return AttributeInformation
     *
     * @throws \InvalidArgumentException When the attribute is not registered.
     */
    public function getAttribute(string $name): AttributeInformationInterface
    {
        if (!$this->hasAttribute($name)) {
            throw new \InvalidArgumentException('Unknown attribute "' . $name . '"');
        }

        return $this->attributes[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes(): array
    {
        return array_values($this->attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributesOfType(string $typeName): \Traversable
    {
        foreach ($this->attributes as $attribute) {
            if ($typeName === $attribute->getType()) {
                yield $attribute;
            }
        }
    }
}
