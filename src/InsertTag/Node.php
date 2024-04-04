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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\InsertTag;

use IteratorAggregate;
use Traversable;

/**
 * An Insert Tag node.
 *
 * @implements IteratorAggregate<int, NodeInterface>
 */
final class Node implements IteratorAggregate, NodeInterface
{
    /** @var list<NodeInterface> */
    private array $parts;

    /** @param NodeInterface ...$parts */
    public function __construct(NodeInterface ...$parts)
    {
        $this->parts = \array_values($parts);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): Traversable
    {
        foreach ($this->parts as $part) {
            yield $part;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function asString(): string
    {
        return '{{' . \array_reduce(
            $this->parts,
            fn($result, $arg) => $result . $arg->asString(),
            ''
        ) . '}}';
    }
}
