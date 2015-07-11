<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
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
     * Event when a filter setting factory is created.
     *
     * @see \MetaModels\Filter\Setting\Events\CreateRenderSettingFactoryEvent
     */
    const RENDER_SETTING_FACTORY_CREATE = 'metamodels.render-setting.factory.create';

    /**
     * Event when an item is parsed.
     *
     * @see \MetaModels\Events\ParseItemEvent.
     */
    const PARSE_ITEM = 'metamodels.parse-item';
}
