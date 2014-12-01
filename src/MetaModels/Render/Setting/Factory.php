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

namespace MetaModels\Render\Setting;

use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;

/**
 * This is the factory implementation.
 *
 * To create a render settings instance, call {@link Factory::byId()}
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
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
