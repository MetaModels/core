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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

/**
 * This interface handles filter setting abstraction for settings that can contain children.
 */
interface IWithChildren extends ISimple
{
    /**
     * Adds a child setting to this setting.
     *
     * @param ISimple $objFilterSetting The setting that shall be added as child.
     *
     * @return void
     */
    public function addChild(ISimple $objFilterSetting);
}
