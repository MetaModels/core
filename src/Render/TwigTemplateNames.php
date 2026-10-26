<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\Render;

use function array_unique;
use function array_values;
use function preg_match;
use function str_starts_with;
use function substr;

/**
 * Maps between the legacy MetaModels template names ("mm_attr_text") and the identifiers of their Twig counterparts
 * ("metamodels/attribute/text").
 */
final class TwigTemplateNames
{
    /**
     * The legacy name prefixes by Twig group. The first one is used to build a legacy name from an identifier.
     */
    private const PREFIXES = [
        'attribute' => ['mm_attr_'],
        'filter'    => ['mm_filteritem_', 'mm_filter_', 'mm_clearall_'],
        'item'      => ['metamodel_', 'mm_'],
    ];

    /**
     * Build the Twig identifier ("metamodels/<group>/<leaf>") for a legacy template name.
     *
     * The leaf is the template name with the conventional legacy prefix removed.
     *
     * @return string|null The identifier or null when no Twig group is given.
     */
    public static function identifier(string $templateName, string $group): ?string
    {
        if ('' === $group || '' === $templateName) {
            return null;
        }

        $leaf = $templateName;
        foreach (self::PREFIXES[$group] ?? [] as $prefix) {
            if (str_starts_with($leaf, $prefix)) {
                $leaf = substr($leaf, \strlen($prefix));
                break;
            }
        }

        return 'metamodels/' . $group . '/' . $leaf;
    }

    /**
     * Derive the legacy template names of the Twig templates that start with the given base.
     *
     * Only the visual templates of the groups "attribute" and "item" are considered, the plain text variants
     * ("<leaf>.text") have no choice of their own. The filter group is left out as its legacy names do not tell which
     * prefix they belong to.
     *
     * @param list<string> $identifiers The identifiers known to the Twig loader.
     * @param string       $base        The legacy base name, e.g. "mm_attr_text".
     *
     * @return list<string>
     */
    public static function legacyNames(array $identifiers, string $base): array
    {
        $names = [];
        foreach ($identifiers as $identifier) {
            if (1 !== preg_match('#^metamodels/(attribute|item)/([^/.]+)$#', $identifier, $matches)) {
                continue;
            }

            $name = self::PREFIXES[$matches[1]][0] . $matches[2];
            if (str_starts_with($name, $base)) {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }
}
