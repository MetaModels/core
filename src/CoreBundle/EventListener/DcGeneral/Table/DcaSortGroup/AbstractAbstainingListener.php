<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
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
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSortGroup;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
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
    private RequestScopeDeterminator $scopeDeterminator;

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

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if ('tl_metamodel_dca_sortgroup' !== $dataDefinition->getName()) {
            return false;
        }

        if ($event instanceof AbstractModelAwareEvent) {
            if ($dataDefinition->getName() !== $event->getModel()->getProviderName()) {
                return false;
            }
        }

        return true;
    }
}
