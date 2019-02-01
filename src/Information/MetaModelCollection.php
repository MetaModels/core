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
class MetaModelCollection implements MetaModelCollectionInterface
{
    /**
     * The list of configured MetaModels.
     *
     * @var MetaModelInformationInterface[]
     */
    private $metaModels = [];

    /**
     * {@inheritDoc}
     */
    public function getNames(): array
    {
        return array_keys($this->metaModels);
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return array_values($this->metaModels);
    }

    /**
     * Add information for a MetaModel.
     *
     * @param MetaModelInformationInterface $information The MetaModel information.
     *
     * @return void
     *
     * @throws \InvalidArgumentException When the MetaModel is already registered.
     */
    public function add(MetaModelInformationInterface $information): void
    {
        if ($this->has($name = $information->getName())) {
            throw new \InvalidArgumentException('MetaModel "' . $name . '" already registered');
        }

        $this->metaModels[$name] = $information;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->metaModels);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException When the MetaModel is not registered.
     */
    public function get(string $name): MetaModelInformationInterface
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException('Unknown MetaModel "' . $name . '"');
        }

        return $this->metaModels[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        // Cannot "yield from" here as we have the names as key.
        /** @noinspection YieldFromCanBeUsedInspection */
        foreach ($this->metaModels as $metaModel) {
            yield $metaModel;
        }
    }
}
