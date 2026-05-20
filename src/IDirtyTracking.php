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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

/**
 * Optional interface for MetaModel items that support dirty tracking.
 *
 * Tracks which attributes were explicitly set after item construction.
 * Items loaded from the database are not considered dirty until explicitly
 * modified via set(). This prevents fallback-language data from being
 * written to the active language on save.
 *
 * This is an optional interface — code must check instanceof before calling isDirty().
 */
interface IDirtyTracking
{
    /**
     * Check if the given attribute was explicitly set after item loading.
     *
     * Returns true only for attributes explicitly set via set() after construction,
     * not for values loaded from the database during item fetch.
     *
     * @param string $attributeName The desired attribute.
     *
     * @return bool True if the attribute was explicitly set/modified, false if only from initial load.
     */
    public function isDirty(string $attributeName): bool;
}
