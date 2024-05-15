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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Sorter;

use MetaModels\Attribute\IAttribute;

/**
 * This sort attributes.
 */
final class AttributeSorter
{
    /**
     * Sort by name.
     *
     * @param array  $attributes The attributes.
     * @param string $dir        The sort direction.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function sortByName(array $attributes, string $dir = 'ASC'): array
    {
        if ('ASC' === \strtoupper($dir)) {
            \usort($attributes, fn($a, $b) => $a->getName() <=> $b->getName());
        }

        if ('DESC' === \strtoupper($dir)) {
            \usort($attributes, fn($a, $b) => $b->getName() <=> $a->getName());
        }

        return $attributes;
    }

    /**
     * Sort by column name.
     *
     * @param array  $attributes The attributes.
     * @param string $dir        The sort direction.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function sortByColumnName(array $attributes, string $dir = 'ASC'): array
    {
        if ('ASC' === \strtoupper($dir)) {
            \usort($attributes, fn($a, $b) => $a->getColName() <=> $b->getColName());
        }

        if ('DESC' === \strtoupper($dir)) {
            \usort($attributes, fn($a, $b) => $b->getColName() <=> $a->getColName());
        }

        return $attributes;
    }
}
