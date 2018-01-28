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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSettingCondition;

use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;

/**
 * This handles the type options for conditions.
 */
class PasteButtonListener extends AbstractListener
{
    /**
     * Generate the paste button.
     *
     * @param GetPasteButtonEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handle(GetPasteButtonEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $environment = $event->getEnvironment();
        $model       = $event->getModel();
        $clipboard   = $environment->getClipboard();
        // Disable all buttons if there is a circular reference.
        if ($clipboard->fetch(
            Filter::create()->andActionIs(ItemInterface::CUT)->andModelIs(ModelId::fromModel($model))
        )) {
            $event
                ->setPasteAfterDisabled(true)
                ->setPasteIntoDisabled(true);

            return;
        }

        // FIXME: should be obtained from factory or parameter.
        $flags = $GLOBALS['METAMODELS']['inputscreen_conditions'][$model->getProperty('type')];
        // If setting does not support children, omit them.
        if ($model->getId() &&
            (!$flags['nestingAllowed'])
        ) {
            $event->setPasteIntoDisabled(true);
            return;
        }

        $collector = new ModelCollector($environment);
        if (isset($flags['maxChildren']) && count($collector->collectChildrenOf($model)) > $flags['maxChildren']) {
            $event->setPasteIntoDisabled(true);
        }
    }
}
