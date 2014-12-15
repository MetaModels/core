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

namespace MetaModels\Filter\Setting\Events;

use MetaModels\Filter\Setting\LegacyFilterSettingTypeFactory;

/**
 * This class implements the fallback implementation of the MetaModels Version 1.X filter setting factory.
 *
 * @deprecated You should refactor every filter setting to implement an event listener to create new instances.
 */
class LegacyListener
{
    /**
     * Register all legacy factories and all types defined via the legacy array as a factory.
     *
     * @param CreateFilterSettingFactoryEvent $event The event.
     *
     * @return void
     *
     * @deprecated This method is part of the backwards compatible layer.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function registerLegacyFactories(CreateFilterSettingFactoryEvent $event)
    {
        if (empty($GLOBALS['METAMODELS']['filters'])) {
            return;
        }
        $factory = $event->getFactory();

        foreach ($GLOBALS['METAMODELS']['filters'] as $typeName => $filterSettingInformation) {
            $typeFactory = LegacyFilterSettingTypeFactory::createLegacyFactory($typeName, $filterSettingInformation);

            $factory->addTypeFactory($typeFactory);
        }
    }
}
