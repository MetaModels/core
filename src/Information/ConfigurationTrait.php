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
 * This helps writing key/value stores.
 */
trait ConfigurationTrait
{
    /**
     * The configuration values.
     *
     * @var array
     */
    private $configuration = [];

    /**
     * Retrieve configuration.
     *
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * Check if a configuration value exists.
     *
     * @param string $name The name of the configuration value to test.
     *
     * @return bool
     */
    public function hasConfigurationValue(string $name): bool
    {
        return array_key_exists($name, $this->configuration);
    }

    /**
     * Obtain a single configuration value.
     *
     * @param string $name The name of the configuration value to obtain.
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException When the configuration key does not exist.
     */
    public function getConfigurationValue(string $name)
    {
        if (!$this->hasConfigurationValue($name)) {
            throw new \InvalidArgumentException('Configuration key "' . $name . '" does not exist');
        }

        return $this->configuration[$name];
    }

    /**
     * Add configuration values from the passed array.
     *
     * @param array $values The new values.
     *
     * @return void
     */
    public function addConfiguration(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->configuration[$key] = $value;
        }
    }
}
