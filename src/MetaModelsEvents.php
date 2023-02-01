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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

/**
 * This class holds all event names.
 */
class MetaModelsEvents
{
    /**
     * Event for booting a MetaModels subsystem (is fired prior to the event for the current runtime environment).
     *
     * @see \MetaModels\Events\MetaModelsBootEvent
     *
     * @see MetaModelsEvents::SUBSYSTEM_BOOT_FRONTEND
     *
     * @see MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND
     */
    const SUBSYSTEM_BOOT = 'metamodels.boot';

    /**
     * Event for booting a MetaModels subsystem in the frontend.
     *
     * This is fired after @link(MetaModelsEvents::SUBSYSTEM_BOOT) has been fired
     *
     * @see \MetaModels\Events\MetaModelsBootEvent
     */
    const SUBSYSTEM_BOOT_FRONTEND = 'metamodels.boot.frontend';

    /**
     * Event for booting a MetaModels subsystem in the backend.
     *
     * This is fired after @link(MetaModelsEvents::SUBSYSTEM_BOOT) has been fired
     *
     * @see \MetaModels\Events\MetaModelsBootEvent
     */
    const SUBSYSTEM_BOOT_BACKEND = 'metamodels.boot.backend';

    /**
     * Event when a attribute factory is created.
     *
     * @see \MetaModels\Attribute\Events\CreateAttributeFactoryEvent
     */
    const ATTRIBUTE_FACTORY_CREATE = 'metamodels.attribute.factory.create';

    /**
     * Event when a filter setting factory is created.
     *
     * @see \MetaModels\Filter\Setting\Events\CreateFilterSettingFactoryEvent
     */
    const FILTER_SETTING_FACTORY_CREATE = 'metamodels.filter-setting.factory.create';

    /**
     * Event when a render setting factory is created.
     *
     * @see \MetaModels\Render\Setting\Events\CreateRenderSettingFactoryEvent
     */
    const RENDER_SETTING_FACTORY_CREATE = 'metamodels.render-setting.factory.create';

    /**
     * Event when an item is parsed.
     *
     * @see \MetaModels\Events\ParseItemEvent.
     */
    const PARSE_ITEM = 'metamodels.parse-item';

    /**
     * Event when an item list is rendered.
     *
     * @see \MetaModels\Events\RenderItemListEvent.
     */
    const RENDER_ITEM_LIST = 'metamodels.render-item-list';
}
