<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\Item;

/**
 * A column-name-indexed view onto one output format ("text", "html5", ...) of a parsed item.
 *
 * Stands in for the plain array that Item::parseValue() used to build eagerly for every attribute
 * in the render setting. Accessing a column here renders that one attribute, in exactly this one
 * format, on the spot - see ".claude/lazy-attribut-rendering.md" for the background.
 *
 * Each format gets its own resolver, deliberately not shared with sibling views of the same
 * attribute (an earlier version shared one resolver between the "text" and format views, which
 * meant accessing either rendered both - defeating the point when a template only ever reads one
 * of them). Rendering an attribute through more than one view therefore does render it more than
 * once; that only happens when a template genuinely uses both formats of the same attribute.
 *
 * Read-only by design: offsetSet()/offsetUnset() throw, so that code relying on being able to
 * mutate the result fails loudly instead of silently disagreeing with the cache. Installations
 * hitting this should disable "lazyAttributeRendering" on the render setting instead.
 *
 * @implements \ArrayAccess<string, mixed>
 * @implements \IteratorAggregate<string, mixed>
 */
final class LazyAttributeValues implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * Create a new instance.
     *
     * @param LazyAttributeResultResolver $resolver Renders and caches one attribute's full result.
     * @param string                      $subKey   The result key this view exposes ("text", the
     *                                               requested output format, ...).
     * @param list<string>                $colNames The column names known to the render setting,
     *                                               in the order they should be rendered/iterated.
     */
    public function __construct(
        private readonly LazyAttributeResultResolver $resolver,
        private readonly string $subKey,
        private readonly array $colNames,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * Mirrors array_key_exists() on the eager result, including "hideEmptyValues" having removed
     * the key - both require rendering the attribute to know.
     */
    #[\Override]
    public function offsetExists(mixed $offset): bool
    {
        return \is_string($offset)
            && \in_array($offset, $this->colNames, true)
            && \array_key_exists($this->subKey, $this->resolver->resolve($offset));
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->resolver->resolve((string) $offset)[$this->subKey] ?? null;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \LogicException Always - see the class docblock.
     */
    #[\Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException(
            'LazyAttributeValues is read-only. Disable "lazyAttributeRendering" on the render '
            . 'setting if code needs to write into a parsed item result.'
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws \LogicException Always - see the class docblock.
     */
    #[\Override]
    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException(
            'LazyAttributeValues is read-only. Disable "lazyAttributeRendering" on the render '
            . 'setting if code needs to remove keys from a parsed item result.'
        );
    }

    /**
     * {@inheritDoc}
     *
     * Matches count() on the eager result exactly, which means resolving every column - a caller
     * that only needs to know whether anything is present at all should prefer offsetExists() on
     * the specific column it cares about.
     */
    #[\Override]
    public function count(): int
    {
        $count = 0;
        foreach ($this->colNames as $colName) {
            if ($this->offsetExists($colName)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * {@inheritDoc}
     *
     * Resolves every column, same trade-off as count().
     *
     * @return \Iterator<string, mixed>
     */
    #[\Override]
    public function getIterator(): \Iterator
    {
        foreach ($this->colNames as $colName) {
            if ($this->offsetExists($colName)) {
                yield $colName => $this->offsetGet($colName);
            }
        }
    }
}
