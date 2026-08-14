<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetEditMaskSubHeadlineEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use MetaModels\CoreBundle\Backend\ItemLabelRenderer;
use MetaModels\DcGeneral\DataDefinition\Definition\IMetaModelDefinition;
use MetaModels\ViewCombination\InputScreenInformationBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This handles the additional part of sub-headline in input mask.
 */
final class EditMaskSubHeadlineListener
{
    /**
     * The input screen information builder.
     *
     * @var InputScreenInformationBuilder
     */
    private InputScreenInformationBuilder $inputScreens;

    /**
     * The token parser.
     *
     * @var ItemLabelRenderer
     */
    private ItemLabelRenderer $labelRenderer;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * EditMaskSubHeadlineListener constructor.
     *
     * @param InputScreenInformationBuilder $inputScreens The input screen information builder.
     * @param ItemLabelRenderer             $labelRenderer The renderer for the record label.
     * @param TranslatorInterface           $translator   The translator.
     */
    public function __construct(
        InputScreenInformationBuilder $inputScreens,
        ItemLabelRenderer $labelRenderer,
        TranslatorInterface $translator
    ) {
        $this->inputScreens  = $inputScreens;
        $this->labelRenderer = $labelRenderer;
        $this->translator    = $translator;
    }

    /**
     * Set sub-headline.
     *
     * @param GetEditMaskSubHeadlineEvent $event The sub-headline event.
     */
    public function __invoke(GetEditMaskSubHeadlineEvent $event): void
    {
        $environment    = $event->getEnvironment();
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (!\str_starts_with($dataDefinition->getName(), 'mm_')) {
            return;
        }

        // Nothing to do on create item.
        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);
        if ('create' === $inputProvider->getParameter('act')) {
            return;
        }

        // Retrieve the settings of the input mask for item attribute.
        $metaModel = $dataDefinition->getDefinition(IMetaModelDefinition::NAME);
        assert($metaModel instanceof IMetaModelDefinition);
        $metaModelName = $dataDefinition->getName();
        $screen        = $this->inputScreens->fetchInputScreens([$metaModelName => $metaModel->getActiveInputScreen()]);
        $screenMeta    = $screen[$metaModelName]['meta'] ?? null;

        if (null === $screenMeta || '' === ($headline = ($screenMeta['subheadline'] ?? ''))) {
            return;
        }

        $headlineAdd = $this->labelRenderer->render($headline, $event->getModel()->getPropertiesAsArray());

        // Translate language key and add headline part.
        $subHeadline =
            $this->translator->trans('editRecord', ['%id%' => $headlineAdd], $metaModelName);

        $event->setHeadline($subHeadline);
    }
}
