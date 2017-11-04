<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Dca;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;

/**
 * This renders the model in the backend.
 */
class ModelRenderListener
{
    /**
     * Render the html for the input screen.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public function handle(ModelToLabelEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca')) {
            return;
        }

        $environment = $event->getEnvironment();
        $translator  = $environment->getTranslator();
        $model       = $event->getModel();

        if (!$model->getProperty('isdefault')) {
            return;
        }

        $event
            ->setArgs(array_merge($event->getArgs(), array($translator->translate('MSC.fallback'))))
            ->setLabel(sprintf('%s <span style="color:#b3b3b3; padding-left:3px">[%%s]</span>', $event->getLabel()));
    }
}
