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

namespace MetaModels\Schema;

use InvalidArgumentException;

/**
 * This encapsulates the different schema engine information.
 */
class SchemaInformation
{
    /**
     * The list of registered schema information.
     *
     * @var array<string, SchemaInformationInterface>
     */
    private array $information = [];

    /**
     * Retrieve schema information.
     *
     * @param string $name The information to retrieve the schema for.
     *
     * @return SchemaInformationInterface
     *
     * @throws InvalidArgumentException When the information is not registered.
     */
    public function get(string $name): SchemaInformationInterface
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException('Information with name "' . $name . '" not registered.');
        }

        return $this->information[$name];
    }

    /**
     * Test if the information with the passed name is registered.
     *
     * @param string $name The name of the information to search.
     */
    public function has(string $name): bool
    {
        return isset($this->information[$name]);
    }

    /**
     * Test if the information with the passed name is registered.
     *
     * @param SchemaInformationInterface $information The information to add.
     *
     * @throws InvalidArgumentException When the information is already registered.
     */
    public function add(SchemaInformationInterface $information): void
    {
        if ($this->has($name = $information->getName())) {
            throw new InvalidArgumentException('Information with name "' . $name . '" already registered.');
        }

        $this->information[$information->getName()] = $information;
    }

    /**
     * Obtain the list of registered names.
     *
     * @return list<string>
     */
    public function getRegisteredNames(): array
    {
        return array_keys($this->information);
    }
}
