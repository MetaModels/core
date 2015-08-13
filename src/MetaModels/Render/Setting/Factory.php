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

namespace MetaModels\Render\Setting;

use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;

/**
 * This is the factory implementation.
 *
 * To create a render settings instance, call {@link Factory::byId()}
 *
 * @deprecated Utilize the factory from the service container.
 */
class Factory implements IFactory
{
    /**
     * {@inheritdoc}
     *
     * @deprecated Utilize the factory from the service container.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function collectAttributeSettings(IMetaModel $objMetaModel, $objSetting)
    {
        /** @var IMetaModelsServiceContainer $serviceContainer */
        $serviceContainer = $GLOBALS['container']['metamodels-service-container'];

        return $serviceContainer->getRenderSettingFactory()->collectAttributeSettings($objMetaModel, $objSetting);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Utilize the factory from the service container.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function byId(IMetaModel $objMetaModel, $intId = 0)
    {
        /** @var IMetaModelsServiceContainer $serviceContainer */
        $serviceContainer = $GLOBALS['container']['metamodels-service-container'];

        return $serviceContainer->getRenderSettingFactory()->createCollection(
            $objMetaModel,
            $intId === 0 ? '' : $intId
        );
    }
}
