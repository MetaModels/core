<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
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
 * @author     Christopher Boelter <c.boelter@cogizz.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Events;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostCreateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;

/**
 * Central event subscriber implementation.
 */
class Subscriber extends BaseSubscriber
{
    /**
     * Register all listeners to handle creation of a data container.
     *
     * @return void
     */
    protected function registerEventsInDispatcher()
    {
        $this->registerTableWatcher();
    }

    /**
     * Check if we have to purge the MetaModels cache.
     *
     * @param AbstractModelAwareEvent $event The event holding the model being manipulated.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function checkPurge(AbstractModelAwareEvent $event)
    {
        $table = $event->getModel()->getProviderName();
        if (($table == 'tl_metamodel') ||
            ($table == 'tl_metamodel_dca') ||
            ($table == 'tl_metamodel_dca_sortgroup') ||
            ($table == 'tl_metamodel_dcasetting') ||
            ($table == 'tl_metamodel_dcasetting_condition') ||
            ($table == 'tl_metamodel_attribute') ||
            ($table == 'tl_metamodel_filter') ||
            ($table == 'tl_metamodel_filtersetting') ||
            ($table == 'tl_metamodel_rendersettings') ||
            ($table == 'tl_metamodel_rendersetting') ||
            ($table == 'tl_metamodel_dca_combine')
        ) {
            $purger = \Contao\System::getContainer()->get('metamodels.cache.purger');
            $purger->purge();
        }
    }

    /**
     * Register event to clear the cache when a relevant data model has been saved.
     *
     * @return void
     */
    private function registerTableWatcher()
    {
        $this
            ->addListener(PostCreateModelEvent::NAME, array($this, 'checkPurge'))
            ->addListener(PostDeleteModelEvent::NAME, array($this, 'checkPurge'))
            ->addListener(PostDuplicateModelEvent::NAME, array($this, 'checkPurge'))
            ->addListener(PostPasteModelEvent::NAME, array($this, 'checkPurge'))
            ->addListener(PostPersistModelEvent::NAME, array($this, 'checkPurge'));
    }
}
