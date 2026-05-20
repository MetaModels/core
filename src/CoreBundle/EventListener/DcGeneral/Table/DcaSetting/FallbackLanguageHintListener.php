<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use MetaModels\Attribute\ITranslated;
use MetaModels\IFactory;
use MetaModels\ITranslatedMetaModel;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Adds a label hint to every translated-attribute widget that implements
 * {@see ITranslated}, indicating whether the displayed value is an
 * own translation ("[Tx]", green) or comes from the fallback language ("[Fb]", yellow).
 * Works independently of any machine-translation provider.
 *
 * FIXME: AI Bullshit! should never be the case! If it is, the attribute is WRONG!
 * Attributes opt in by implementing {@see ITranslationHintSupport}.  Attributes
 * that only implement the base {@see \MetaModels\Attribute\ITranslated} (e.g. TranslatedSelect,
 * TranslatedTags) are skipped because their getTranslatedDataFor() may silently return
 * fallback data, making a reliable distinction impossible.
 */
final class FallbackLanguageHintListener
{
    public function __construct(
        private readonly RequestScopeDeterminator $scopeDeterminator,
        private readonly IFactory $factory,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function handle(ManipulateWidgetEvent $event): void
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return;
        }

        $context = $this->resolveContext($event);
        if (null === $context) {
            return;
        }

        [$attribute, $targetLang, $sourceLang] = $context;

        $fromFallback = $this->isFromFallback($event->getModel()->getId(), $attribute, $targetLang);
        $event->getWidget()->xlabel .= $this->buildHint($fromFallback, $sourceLang, $targetLang);
    }

    /** @return array{0: ITranslated, 1: string, 2: string}|null */
    private function resolveContext(ManipulateWidgetEvent $event): ?array
    {
        $environment    = $event->getEnvironment();
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $tableName = $dataDefinition->getName();
        if (!\str_starts_with($tableName, 'mm_')) {
            return null;
        }

        $metaModel = $this->factory->getMetaModel($tableName);
        if (!($metaModel instanceof ITranslatedMetaModel)) {
            return null;
        }

        $dataProvider = $environment->getDataProvider($event->getModel()->getProviderName());
        if (!($dataProvider instanceof MultiLanguageDataProviderInterface)) {
            return null;
        }

        $targetLang = $dataProvider->getCurrentLanguage();
        $sourceLang = $metaModel->getMainLanguage();
        if ($targetLang === $sourceLang) {
            return null;
        }

        $attribute = $metaModel->getAttribute($event->getProperty()->getName());
        if (!($attribute instanceof ITranslated)) {
            return null;
        }

        return [$attribute, $targetLang, $sourceLang];
    }

    private function isFromFallback(mixed $itemId, ITranslated $attribute, string $targetLang): bool
    {
        if (null === $itemId) {
            return true;
        }

        $data = $attribute->getTranslatedDataFor([(string) $itemId], $targetLang);

        return !\array_key_exists((string) $itemId, $data);
    }

    private function buildHint(bool $fromFallback, string $sourceLang, string $targetLang): string
    {
        if ($fromFallback) {
            $label    = $this->translator->trans('fallback_language_hint.label_fallback', [], 'metamodels_default');
            $title    = $this->translator->trans(
                'fallback_language_hint.title_fallback',
                ['%source%' => $sourceLang, '%target%' => $targetLang],
                'metamodels_default',
            );
            $cssClass = 'mm-lang-hint mm-lang-hint--fallback';
        } else {
            $label    = $this->translator->trans('fallback_language_hint.label_translated', [], 'metamodels_default');
            $title    = $this->translator->trans(
                'fallback_language_hint.title_translated',
                ['%target%' => $targetLang],
                'metamodels_default',
            );
            $cssClass = 'mm-lang-hint mm-lang-hint--translated';
        }

        return \sprintf(
            '<span class="%s" title="%s">%s</span>',
            $cssClass,
            \htmlspecialchars($title, \ENT_QUOTES),
            \htmlspecialchars($label, \ENT_QUOTES),
        );
    }
}
