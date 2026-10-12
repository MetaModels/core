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

namespace MetaModels\DcGeneral\Data;

/**
 * The content of a version: the property values of an item and the language they have been taken in.
 *
 * Translated attributes yield language dependent widget values (e.g. tags sorted by label), so a version can only be
 * rebuilt in the language it has been stored in.
 */
final class VersionData
{
    /**
     * The key of the language beside the property values - the '#' can not occur in an attribute column name.
     *
     * It has to stay a flat entry: the Contao version comparison treats every top level entry as a field.
     */
    private const string KEY_LANGUAGE = '#language';

    /**
     * Key of the nested form which has been written by early development versions only.
     */
    private const string KEY_PROPERTIES = '#properties';

    /**
     * Create a new instance.
     *
     * @param string               $language   The language of the property values.
     * @param array<string, mixed> $properties The property values as shown in the edit mask.
     */
    public function __construct(
        public readonly string $language,
        public readonly array $properties
    ) {
    }

    /**
     * Serialize for the version table.
     *
     * @return string
     */
    public function serialize(): string
    {
        return \serialize([self::KEY_LANGUAGE => $this->language] + $this->properties);
    }

    /**
     * Read the data of the version table.
     *
     * @param string $data             The serialized data.
     * @param string $fallbackLanguage The language to assume for versions written before the language was stored.
     *
     * @return self|null Null if the data is no valid version.
     */
    public static function fromSerialized(string $data, string $fallbackLanguage): ?self
    {
        $values = \unserialize($data, ['allowed_classes' => false]);
        if (!\is_array($values)) {
            return null;
        }

        // The nested form of early development versions.
        if (\is_array($values[self::KEY_PROPERTIES] ?? null)) {
            $properties = $values[self::KEY_PROPERTIES];
        } else {
            // Versions written before the language was stored have none and are a plain property list.
            $properties = $values;
        }
        unset($properties[self::KEY_LANGUAGE]);

        $language = $values[self::KEY_LANGUAGE] ?? null;

        return new self(\is_string($language) ? $language : $fallbackLanguage, $properties);
    }

    /**
     * Check that a value is plain data.
     *
     * Attributes handing out objects (e.g. content elements, which are kept in tl_content and versioned there) can
     * neither be stored in a version nor be compared with a restored one.
     *
     * @param mixed $value The widget value.
     *
     * @return bool
     */
    public static function isVersionable(mixed $value): bool
    {
        if (\is_object($value)) {
            return false;
        }

        if (\is_array($value)) {
            foreach ($value as $entry) {
                if (!self::isVersionable($entry)) {
                    return false;
                }
            }
        }

        return true;
    }
}
