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
use Contao\CoreBundle\String\SimpleTokenParser;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
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
     * @var SimpleTokenParser
     */
    private SimpleTokenParser $tokenParser;

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
     * @param SimpleTokenParser             $tokenParser  The token parser.
     * @param TranslatorInterface           $translator   The translator.
     */
    public function __construct(
        InputScreenInformationBuilder $inputScreens,
        SimpleTokenParser $tokenParser,
        TranslatorInterface $translator
    ) {
        $this->inputScreens = $inputScreens;
        $this->tokenParser  = $tokenParser;
        $this->translator   = $translator;
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

        $tokenData = [];
        // Get model properties.
        foreach ($event->getModel()->getPropertiesAsArray() as $keyData => $valueData) {
            $tokenData['model_' . $keyData] = $valueData;
        }

        // Replace simple tokens.
        $headlineAdd = $this->replaceSimpleTokensAtHeadline($headline, $tokenData);

        // Translate language key and add headline part.
        $subHeadline =
            $this->translator->trans('editRecord', ['%id%' => $headlineAdd], $metaModelName);

        $event->setHeadline($subHeadline);
    }

    /**
     * Replace simple tokens at headline parameter.
     *
     * @param string $headline  The headline string.
     * @param array  $tokenData The token data.
     *
     * @return string
     */
    private function replaceSimpleTokensAtHeadline(string $headline, array $tokenData): string
    {
        if (
            \str_contains($headline, '&#35;&#35;')
            || \str_contains($headline, '##')
        ) {
            $headline =
                $this->tokenParser->parse(
                    \str_replace('&#35;', '#', $headline),
                    $tokenData,
                    false
                );
        }

        return $headline;
    }
}
