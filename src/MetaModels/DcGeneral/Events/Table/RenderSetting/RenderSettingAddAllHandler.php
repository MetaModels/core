<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2016 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\RenderSetting;

use MetaModels\Attribute\IAttribute;
use MetaModels\DcGeneral\Events\Table\AbstractAddAllHandler;

/**
 * This class handles the add all action for render settings.
 */
class RenderSettingAddAllHandler extends AbstractAddAllHandler
{
    /**
     * The table name to work on.
     *
     * @var string
     */
    protected static $table = 'tl_metamodel_rendersetting';

    /**
     * The parent table name to work on.
     *
     * @var string
     */
    protected static $ptable = 'tl_metamodel_rendersettings';

    /**
     * The field to use for activating.
     *
     * @var string
     */
    protected static $activeField = 'enabled';

    /**
     * The action name to listen on.
     *
     * @var string
     */
    protected static $actionName = 'rendersetting_addall';

    /**
     * Create an empty data set for inclusion into the database.
     *
     * @param IAttribute $attribute The attribute to generate the data for.
     *
     * @return mixed
     */
    protected function createEmptyDataFor($attribute)
    {
        $result   = array();
        $defaults = $attribute->getDefaultRenderSettings();
        foreach ($defaults->getKeys() as $key) {
            $result[$key] = $defaults->get($key);
        }

        return $result;
    }

    /**
     * Test if the passed attribute is acceptable.
     *
     * @param IAttribute $attribute The attribute to check.
     *
     * @return bool
     */
    protected function accepts($attribute)
    {
        if (!$attribute->get('id')) {
            return false;
        }

        return true;
    }
}
