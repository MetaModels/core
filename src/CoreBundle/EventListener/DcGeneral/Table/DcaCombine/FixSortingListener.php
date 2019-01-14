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
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaCombine;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;

/**
 * This class optimizes the sorting values.
 */
class FixSortingListener
{
    /**
     * Handle event to update the sorting for DCA combinations.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function handle(EncodePropertyValueFromWidgetEvent $event)
    {
        if (('tl_metamodel_dca_combine' !== $event->getEnvironment()->getDataDefinition()->getName())
            || ('rows' !== $event->getProperty())) {
            return;
        }

        $values = $event->getValue();
        $index  = 0;
        $time   = time();
        foreach (array_keys($values) as $key) {
            $values[$key]['sorting'] = $index;
            $values[$key]['tstamp']  = $time;

            $index += 128;
        }

        $event->setValue($values);
    }
}
