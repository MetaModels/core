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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;

/**
 * This provides the render mode options.
 */
class RenderModeOptionListener
{
    /**
     * Retrieve a list of all backend sections, like "content", "system" etc.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handle(GetPropertyOptionsEvent $event)
    {
        if (('tl_metamodel_dca' !== $event->getEnvironment()->getDataDefinition()->getName())
            || ('rendermode' !== $event->getPropertyName())) {
            return;
        }

        $translator = $event->getEnvironment()->getTranslator();
        $options    = [
            'flat'         => $translator->translate('rendermodes.flat', 'tl_metamodel_dca'),
            'hierarchical' => $translator->translate('rendermodes.hierarchical', 'tl_metamodel_dca'),
        ];

        if ('ctable' === $event->getModel()->getProperty('rendertype')) {
            $options['parented'] = $translator->translate('rendermodes.parented', 'tl_metamodel_dca');
        }

        $event->setOptions($options);
    }
}
