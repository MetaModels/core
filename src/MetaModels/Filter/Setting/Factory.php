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
 * @author     David Maack <david.maack@arcor.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use MetaModels\IMetaModelsServiceContainer;

/**
 * This is the IMetaModelFilter factory interface.
 *
 * To create a IMetaModelFilter instance, call {@link MetaModelFilter::byId()}
 *
 * @deprecated use the factory from the service container.
 */
class Factory extends FilterSettingFactory implements IFactory
{
    /**
     * Keeps track of all filter settings instances to save DB lookup queries.
     *
     * @var ICollection[]
     */
    protected static $arrInstances = array();

    /**
     * Create a IMetaModelFilter instance from the id.
     *
     * @param int $intId The id of the IMetaModelFilter.
     *
     * @return ICollection the instance of the IMetaModelFilterSettings or null if not found.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     *
     * @deprecated use the factory from the service container.
     */
    public static function byId($intId)
    {
        /** @var IMetaModelsServiceContainer $serviceContainer */
        $serviceContainer = $GLOBALS['container']['metamodels-service-container'];

        return $serviceContainer->getFilterFactory()->createCollection($intId);
    }
}
