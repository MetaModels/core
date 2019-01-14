<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Render\Setting;

use MetaModels\IMetaModel;
use MetaModels\IServiceContainerAware;

/**
 * This is the filter settings factory interface.
 *
 * @see IRenderSettingFactory::createCollection() to create a render setting collection instance.
 */
interface IRenderSettingFactory extends IServiceContainerAware
{
    /**
     * Create a ICollection instance from the id.
     *
     * @param IMetaModel $metaModel The MetaModel for which to retrieve the render setting.
     *
     * @param string     $settingId The id of the ICollection.
     *
     * @return ICollection The instance or null if not found.
     */
    public function createCollection(IMetaModel $metaModel, $settingId = '');
}
