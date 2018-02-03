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

namespace MetaModels\CoreBundle\EventListener;

use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;
use MetaModels\BackendIntegration\PurgeCache;

/**
 * Central event subscriber implementation.
 */
class PurgeListener
{
    /**
     * The cache purger.
     *
     * @var PurgeCache
     */
    private $purger;

    /**
     * Create a new instance.
     *
     * @param PurgeCache $purger The cache purger.
     */
    public function __construct(PurgeCache $purger)
    {
        $this->purger = $purger;
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
            $this->purger->purge();
        }
    }
}
