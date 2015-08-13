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

/**
 * This is the filter settings factory interface.
 *
 * To create a filter settings instance, call {@link \MetaModels\Filter\Setting\Factory::byId()}
 */
interface IFactory extends IFilterSettingFactory
{
    /**
     * Create a IMetaModelFilterSettings instance from the id.
     *
     * @param int $intId The id of the IMetaModelFilterSettings.
     *
     * @return ICollection The instance of the filter settings or null if not found.
     *
     * @deprecated Will get moved to a real factory.
     */
    public static function byId($intId);
}
