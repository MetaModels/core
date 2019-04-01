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

namespace MetaModels\DcGeneral\Events\Table\InputScreens;

use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IInternal;
use MetaModels\DcGeneral\Events\Table\AbstractAddAllHandler;

/**
 * This class handles the add all action for render settings.
 */
class InputScreenAddAllHandler extends AbstractAddAllHandler
{
    /**
     * The table name to work on.
     *
     * @var string
     */
    protected static $table = 'tl_metamodel_dcasetting';

    /**
     * The parent table name to work on.
     *
     * @var string
     */
    protected static $ptable = 'tl_metamodel_dca';

    /**
     * The field to use for activating.
     *
     * @var string
     */
    protected static $activeField = 'published';

    /**
     * The action name to listen on.
     *
     * @var string
     */
    protected static $actionName = 'dca_addall';

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function createEmptyDataFor($attribute)
    {
        return array(
            'dcatype'  => 'attribute',
            'tl_class' => '',
        );
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
        return !($attribute instanceof IInternal) && $attribute->get('id');
    }
}
