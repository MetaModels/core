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
 * This interface describes MetaModel attributes.
 */
interface AttributeInformationInterface
{
    /**
     * Obtain the internal name of the attribute.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the type name.
     *
     * @return string
     */
    public function getType(): string;

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
}
