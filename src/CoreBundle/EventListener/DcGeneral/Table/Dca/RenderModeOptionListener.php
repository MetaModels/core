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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Dca;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;

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
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (
            ('tl_metamodel_dca' !== $dataDefinition->getName())
            || ('rendermode' !== $event->getPropertyName())
        ) {
            return;
        }

        $translator = $event->getEnvironment()->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $options = [
            'flat'         => $translator->translate('rendermodes.flat', 'tl_metamodel_dca'),
            'hierarchical' => $translator->translate('rendermodes.hierarchical', 'tl_metamodel_dca'),
        ];

        if ('ctable' === $event->getModel()->getProperty('rendertype')) {
            $options['parented'] = $translator->translate('rendermodes.parented', 'tl_metamodel_dca');
        }

        $event->setOptions($options);
    }
}
