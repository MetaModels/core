<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\InsertTag;

use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, NodeInterface>
 */
final class NodeList implements IteratorAggregate, NodeInterface
{
    /** @var list<NodeInterface> */
    private array $elements;

    public function __construct(NodeInterface ...$elements)
    {
        $this->elements = $elements;
    }

    public function getIterator(): Traversable
    {
        foreach ($this->elements as $element) {
            yield $element;
        }
    }

    public function asString(): string
    {
        return array_reduce(
            $this->elements,
            fn(string $result, NodeInterface $arg) => $result . $arg->asString(),
            ''
        );
    }
}
