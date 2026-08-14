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

namespace MetaModels\CoreBundle\Backend;

use Contao\CoreBundle\String\SimpleTokenParser;

/**
 * Names a single record of a MetaModel, following the "subheadline" of its input screen.
 *
 * The pattern is written with simple tokens over the properties of the record - "##model_name##"
 * for an attribute, "##model_id##" for the id. It was introduced to add something to the headline
 * of the edit mask; the breadcrumb of a child table needs the very same thing, and both go through
 * here so that they cannot drift apart.
 */
final class ItemLabelRenderer
{
    public function __construct(private readonly SimpleTokenParser $tokenParser)
    {
    }

    /**
     * Build the label of a record.
     *
     * @param string               $pattern    The pattern from the input screen, may be empty.
     * @param array<string, mixed> $properties The properties of the record.
     *
     * @return string The label, or an empty string where no pattern is configured.
     */
    public function render(string $pattern, array $properties): string
    {
        if ('' === $pattern) {
            return '';
        }

        // The editor may have typed the hashes or had them encoded on the way in - both spellings
        // reach the database, so both have to be recognised.
        if (!\str_contains($pattern, '##') && !\str_contains($pattern, '&#35;&#35;')) {
            return $pattern;
        }

        return $this->tokenParser->parse(
            \str_replace('&#35;', '#', $pattern),
            $this->tokens($properties),
            false
        );
    }

    /**
     * Prefix the properties the way the patterns expect them.
     *
     * @param array<string, mixed> $properties The properties of the record.
     *
     * @return array<string, mixed>
     */
    private function tokens(array $properties): array
    {
        $tokens = [];
        foreach ($properties as $name => $value) {
            $tokens['model_' . $name] = $value;
        }

        return $tokens;
    }
}
