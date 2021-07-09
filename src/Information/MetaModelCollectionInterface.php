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
 * This is a collection of MetaModels.
 */
interface MetaModelCollectionInterface extends \IteratorAggregate
{
    /**
     * Obtain the names of the registered MetaModels.
     *
     * @return array
     */
    public function getNames(): array;

    /**
     * Obtain the list of configured MetaModels.
     *
     * @return array
     */
    public function all(): array;

    /**
     * Test if a MetaModel is registered.
     *
     * @param string $name The name of the MetaModel to test.
     *
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Get a MetaModel by name.
     *
     * @param string $name The name to search.
     *
     * @return MetaModelInformationInterface
     *
     * @throws \InvalidArgumentException When the MetaModel is not registered.
     */
    public function get(string $name): MetaModelInformationInterface;

    /**
     * {@inheritDoc}
     *
     * @return \Traversable|MetaModelInformationInterface[]
     */
    public function getIterator();
}
