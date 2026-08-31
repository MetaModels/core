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

namespace MetaModels\CoreBundle\Migration;

/**
 * Adds the column `legacyEagerRendering` to `tl_metamodel_rendersettings` and enables it for all existing rows.
 *
 * Enabling it keeps their output as it was before MetaModels 2.5 started rendering attributes lazily. Both the
 * column and this migration are meant to go with MetaModels 3.0.
 *
 * Background: since 2.5, Item::parseValue() only renders an attribute's template when that attribute is actually
 * accessed, instead of rendering every attribute of the render setting upfront. New render settings therefore
 * start with the column disabled. Render settings that already existed keep the old, eager behaviour, because
 * their installations may carry custom code that assumes the parsed result is a plain array for every attribute
 * in the render setting - see LazyAttributeValues.
 *
 * The distinguishing criterion is not a property of the row but its age: ADD COLUMN plus UPDATE in one run catches
 * exactly the rows that existed at upgrade time. Everything created afterwards falls back to the DCA default.
 */
final class LegacyEagerRenderingMigration extends AbstractAddRenderSettingsColumnMigration
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getName(): string
    {
        return 'Add column legacyEagerRendering to tl_metamodel_rendersettings and enable it for existing rows.';
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    protected function column(): string
    {
        return 'legacyEagerRendering';
    }
}
