<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Attribute\Events;

use MetaModels\Attribute\LegacyAttributeTypeFactory;

/**
 * This class implements the fallback implementation of the MetaModels Version 1.X attribute factory.
 *
 * @deprecated You should refactor every attribute to implement an event listener to create new instances.
 */
class LegacyListener
{
    /**
     * Register all legacy factories and all types defined via the legacy array as a factory.
     *
     * @param CreateAttributeFactoryEvent $event The event.
     *
     * @return void
     *
     * @deprecated This method is part of the backwards compatible layer.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function registerLegacyAttributeFactoryEvents(CreateAttributeFactoryEvent $event)
    {
        if (empty($GLOBALS['METAMODELS']['attributes'])) {
            return;
        }
        $factory = $event->getFactory();

        foreach ($GLOBALS['METAMODELS']['attributes'] as $typeName => $attributeInformation) {
            $typeFactory = LegacyAttributeTypeFactory::createLegacyFactory($typeName, $attributeInformation);

            $factory->addTypeFactory($typeFactory);
        }
    }
}
