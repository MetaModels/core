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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Render\Setting;

use MetaModels\IMetaModel;

/**
 * This is the factory interface for render settings.
 *
 * To create a IFactory instance, call {@link Factory::byId()}
 *
 * @deprecated Use the render setting factory from the service container.
 */
interface IFactory
{
    /**
     * Load all render information from the database and push the contained information into the settings object.
     *
     * You should not call this method directly but rather use {@link IFactory::byId} instead.
     *
     * @param IMetaModel  $objMetaModel The MetaModel information for which the setting shall be retrieved.
     *
     * @param ICollection $objSetting   The render setting instance to be populated.
     *
     * @return void
     *
     * @deprecated Utilize the factory from the service container.
     */
    public static function collectAttributeSettings(IMetaModel $objMetaModel, $objSetting);

    /**
     * Create a ICollection instance from the id.
     *
     * @param IMetaModel $objMetaModel The MetaModel information for which the setting shall be retrieved.
     *
     * @param int        $intId        The id of the ICollection.
     *
     * @return ICollection the instance of the render setting collection or null if not found.
     *
     * @deprecated Utilize the factory from the service container.
     */
    public static function byId(IMetaModel $objMetaModel, $intId = 0);
}
