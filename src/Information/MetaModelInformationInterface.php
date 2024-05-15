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

declare(strict_types=1);

namespace MetaModels\Information;

/**
 * This holds the schema information for a MetaModel.
 */
interface MetaModelInformationInterface
{
    /**
     * Obtain the internal name of the attribute.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Obtain the configuration for the attribute.
     *
     * @return array
     */
    public function getConfiguration(): array;

    /**
     * Check if a configuration value exists.
     *
     * @param string $name The name of the configuration value to test.
     *
     * @return bool
     */
    public function hasConfigurationValue(string $name): bool;

    /**
     * Obtain a single configuration value.
     *
     * @param string $name The name of the configuration value to obtain.
     *
     * @return mixed
     *
     * @throws \RuntimeException When the configuration key does not exist.
     */
    public function getConfigurationValue(string $name);

    /**
     * Obtain the attribute names.
     *
     * @return string[]
     */
    public function getAttributeNames(): array;

    /**
     * Retrieve attributes.
     *
     * @param string $name The name of the attribute.
     *
     * @return AttributeInformationInterface
     *
     * @throws \InvalidArgumentException When the attribute is not registered.
     */
    public function getAttribute(string $name): AttributeInformationInterface;

    /**
     * Test if an attribute has been registered.
     *
     * @param string $name The name of the attribute to test.
     *
     * @return bool
     */
    public function hasAttribute(string $name): bool;

    /**
     * Retrieve attributes.
     *
     * @return list<AttributeInformationInterface>
     */
    public function getAttributes(): array;

    /**
     * Retrieve attributes.
     *
     * @param string $typeName Retrieve all attributes of a certain type.
     *
     * @return \Traversable<int, AttributeInformationInterface>
     */
    public function getAttributesOfType(string $typeName): \Traversable;
}
