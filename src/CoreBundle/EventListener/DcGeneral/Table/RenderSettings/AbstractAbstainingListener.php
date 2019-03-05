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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSettings;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

/**
 * This provides a way to abstain from handling an event.
 */
abstract class AbstractAbstainingListener
{
    /**
     * The scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeDeterminator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        $this->scopeDeterminator = $scopeDeterminator;
    }

    /**
     * Test if the event is for the correct table and in backend scope.
     *
     * @param AbstractEnvironmentAwareEvent $event The event to test.
     *
     * @return bool
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return false;
        }

        $environment = $event->getEnvironment();
        if ('tl_metamodel_rendersettings' !== $environment->getDataDefinition()->getName()) {
            return false;
        }

        if ($event instanceof AbstractModelAwareEvent) {
            if ($event->getEnvironment()->getDataDefinition()->getName() !== $event->getModel()->getProviderName()) {
                return false;
            }
        }

        return true;
    }
}
