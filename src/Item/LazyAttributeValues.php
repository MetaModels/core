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
 * in the render setting. Accessing a column here renders that one attribute on the spot instead of
 * all of them upfront - see ".claude/lazy-attribut-rendering.md" for the background.
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
     * Build the "text" view and the "$subKey" view of one attribute range, sharing one resolver so
     * that an attribute accessed through both views is still only rendered once.
     *
     * Keeps LazyAttributeResultResolver an implementation detail callers do not need to know about
     * - Item::parseValue() only depends on this class, not on the resolver as well.
     *
     * @param \Closure(string): array<string, mixed> $renderer Renders one attribute by column name.
     * @param string                                  $subKey   The second view's result key ("text"
     *                                                           itself is always the first view).
     * @param list<string>                            $colNames The column names known to the render
     *                                                           setting, in rendering/iteration order.
     *
     * @return array{0: self, 1: self} The "text" view, then the "$subKey" view - the same instance
     *                                  twice when $subKey is "text".
     */
    public static function createPair(\Closure $renderer, string $subKey, array $colNames): array
    {
        $resolver = new LazyAttributeResultResolver($renderer);
        $text     = new self($resolver, 'text', $colNames);

        return [$text, 'text' === $subKey ? $text : new self($resolver, $subKey, $colNames)];
    }

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
