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
 * Renders a single attribute on first access and caches the result for every subsequent access.
 *
 * One instance is shared between the "text" and output-format LazyAttributeValues views of a
 * single Item::parseValue() call, so an attribute that is accessed through both views is still
 * only rendered once.
 */
final class LazyAttributeResultResolver
{
    /**
     * The per-attribute results already rendered, keyed by column name.
     *
     * @var array<string, array<string, mixed>>
     */
    private array $resolved = [];

    /**
     * Create a new instance.
     *
     * @param \Closure(string): array<string, mixed> $resolver Renders one attribute by column name.
     */
    public function __construct(private readonly \Closure $resolver)
    {
    }

    /**
     * Render the given attribute, or return the cached result of a previous call.
     *
     * @param string $colName The column name of the attribute to render.
     *
     * @return array<string, mixed>
     */
    public function resolve(string $colName): array
    {
        if (!\array_key_exists($colName, $this->resolved)) {
            $this->resolved[$colName] = ($this->resolver)($colName);
        }

        return $this->resolved[$colName];
    }
}
