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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Dca;

use Contao\Message;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use Doctrine\DBAL\Connection;
use MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting\AbstractListener;
use MetaModels\IFactory;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This adds a hint if render mode hierarchical.
 */
class RenderModeHintListener
{
    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * Create a new instance.
     *
     * @param TranslatorInterface $translator The translator.
     */
    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    /**
     * Adds a hint if render mode hierarchical.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function handle(BuildWidgetEvent $event)
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (
            ($dataDefinition->getName() !== 'tl_metamodel_dca')
            || ($event->getProperty()->getName() !== 'rendermode')
            || (null === $event->getModel()->getId())
        ) {
            return;
        }

        $model = $event->getModel();

        if ('hierarchical' === $model->getProperty('rendermode')) {
            Message::addInfo(
                $this->translator->trans('hint_rendermode_hierarchical', [], 'tl_metamodel_dca')
            );
        }
    }
}
