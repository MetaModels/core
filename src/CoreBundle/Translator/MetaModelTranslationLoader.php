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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Translator;

use Doctrine\DBAL\Exception;
use MetaModels\Attribute\IInternal;
use MetaModels\IFactory;
use MetaModels\ITranslatedMetaModel;
use MetaModels\ViewCombination\InputScreenInformationBuilder;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\Loader\LoaderInterface as SymfonyLoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\TranslatorBagInterface;

use function is_array;
use function sprintf;

final class MetaModelTranslationLoader implements SymfonyLoaderInterface
{
    /**
     * The constructor.
     *
     * @param TranslatorBagInterface        $baseTranslator  The translator interface.
     * @param IFactory                      $factory         The factory.
     * @param ViewCombination               $viewCombination The view combination.
     * @param InputScreenInformationBuilder $builder         The input screen builder.
     * @param list<LoaderInterface>         $loaders         The loaders.
     */
    public function __construct(
        private readonly TranslatorBagInterface $baseTranslator,
        private readonly IFactory $factory,
        private readonly ViewCombination $viewCombination,
        private readonly InputScreenInformationBuilder $builder,
        private readonly array $loaders
    ) {
    }

    /**
     * Load translation catalog.
     *
     * @param mixed  $resource The resource.
     * @param string $locale   The locale.
     * @param string $domain   The domain
     *
     * @return MessageCatalogue
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function load($resource, string $locale, string $domain = 'messages'): MessageCatalogue
    {
        // Load tl_metamodel_item catalogue.
        $base = $this->baseTranslator->getCatalogue($locale);

        $catalog = new MessageCatalogue($locale);

        foreach ($base->all('tl_metamodel_item') as $key => $value) {
            $catalog->set($key, $value, $domain);
        }

        $metaModel = $this->factory->getMetaModel($domain);
        if (null === $metaModel) {
            throw new NotFoundResourceException('Failed to load MetaModel: ' . $domain);
        }

        /**
         * @psalm-suppress DeprecatedMethod
         * @psalm-suppress TooManyArguments
         */
        if ($metaModel instanceof ITranslatedMetaModel) {
            $metaModel->selectLanguage($locale);
            $mainLanguage = $metaModel->getMainLanguage();
        } elseif ($metaModel->isTranslated(false)) {
            $mainLanguage = $metaModel->getFallbackLanguage() ?? 'en';
        } else {
            // Untranslated MetaModel.
            $mainLanguage = 'en';
        }

        // Attributes:
        foreach ($metaModel->getAttributes() as $attribute) {
            if ($attribute instanceof IInternal) {
                continue;
            }
            $colName     = $attribute->getColName();
            $name        = $attribute->get('name') ?? $colName;
            $description = $attribute->get('description') ?? '';
            $catalog->set(
                sprintf('%1$s.label', $colName),
                $this->extractLangString($name, $locale, $mainLanguage) ?? $colName,
                $domain
            );
            $catalog->set(
                sprintf('%1$s.description', $colName),
                $this->extractLangString($description, $locale, $mainLanguage) ?? '',
                $domain
            );
        }

        foreach ($this->builder->fetchAllInputScreensForTable($domain) as $inputScreen) {
            $this->handleInputScreen($domain, $locale, $mainLanguage, $inputScreen, $catalog);
        }

        // Check if we have some children - add child button translations then.
        foreach (\array_keys($this->viewCombination->getChildrenOf($domain)) as $tableName) {
            foreach ($this->builder->fetchAllInputScreensForTable($tableName) as $inputScreen) {
                $this->handleChildInputScreen($domain, $tableName, $locale, $mainLanguage, $inputScreen, $catalog);
            }
        }

        foreach ($this->loaders as $loader) {
            foreach ($loader->load($metaModel, $locale)->all($domain) as $key => $value) {
                $catalog->set($key, $value, $domain);
            }
        }

        return $catalog;
    }

    private function handleChildInputScreen(
        string $domain,
        string $tableName,
        string $locale,
        string $mainLanguage,
        array $inputScreen,
        MessageCatalogue $catalog
    ): void {
        $subMetaModel = $this->factory->getMetaModel($tableName);
        if (null === $subMetaModel) {
            return;
        }
        $translationKey = 'metamodel_edit_as_child.' . $tableName . '.' . $inputScreen['meta']['id'];

        if (
            'metamodel_edit_as_child.label' !==
            $baseValue = $catalog->get('metamodel_edit_as_child.label', $domain)
        ) {
            $catalog->set(
                $translationKey . '.label',
                strtr($baseValue, ['%child_name%' => $subMetaModel->getName()]),
                $domain,
            );
        }
        if (
            'metamodel_edit_as_child.description' !==
            $baseValue = $catalog->get('metamodel_edit_as_child.description', $domain)
        ) {
            $catalog->set(
                $translationKey . '.description',
                strtr($baseValue, ['%child_name%' => $subMetaModel->getName()]),
                $domain,
            );
        }

        $this->setTranslationLabelAndDescription(
            $domain,
            $locale,
            $mainLanguage,
            $translationKey,
            $inputScreen,
            $catalog,
            $tableName,
            ['%child_name%' => $subMetaModel->getName()]
        );
    }

    /**
     * Handle input screen.
     *
     * @param string           $domain       The domain.
     * @param string           $locale       The locale.
     * @param string           $mainLanguage The fallback language.
     * @param array            $inputScreen  The input screen.
     * @param MessageCatalogue $catalog      The catalog.
     *
     * @return void
     */
    private function handleInputScreen(
        string $domain,
        string $locale,
        string $mainLanguage,
        array $inputScreen,
        MessageCatalogue $catalog
    ): void {
        $prefix = 'inputscreen.' . $inputScreen['meta']['id'] . '.';

        foreach ($inputScreen['legends'] as $index => $legend) {
            $value = $this->extractLangString($legend['label'], $locale, $mainLanguage);
            // Suffix '_legend' due to EditMask in DcGeneral.
            if (null !== $value) {
                $catalog->set($prefix . $index . '_legend', $value, $domain);
            }
        }

        if ('standalone' === $inputScreen['meta']['rendertype']) {
            $this->setTranslationLabelAndDescription(
                $domain,
                $locale,
                $mainLanguage,
                $prefix . 'menu',
                $inputScreen,
                $catalog,
                $domain,
                [],
            );
            return;
        }

        if ('ctable' === $inputScreen['meta']['rendertype']) {
            $this->handleChildInputScreen(
                $inputScreen['meta']['ptable'],
                $domain,
                $locale,
                $mainLanguage,
                $inputScreen,
                $catalog,
            );
        }
    }

    /** @param array<string, string> $parameters The parameters. */
    private function setTranslationLabelAndDescription(
        string $domain,
        string $locale,
        string $mainLanguage,
        string $prefix,
        array $inputScreen,
        MessageCatalogue $catalog,
        string $headlineDomain,
        array $parameters,
    ): void {
        $headlineKey = 'backend-module.' . $inputScreen['meta']['id'] . '.headline';
        if (!$catalog->has($prefix . '.description', $domain)) {
            $catalog->set($prefix . '.description', '', $domain);
        }
        if ('' !== $value = $this->extractLangString($inputScreen['description'], $locale, $mainLanguage) ?? '') {
            $value = strtr($value, $parameters);
            $catalog->set($prefix . '.description', $value, $domain);

            if ($headlineKey === $catalog->get($headlineKey, $headlineDomain)) {
                $catalog->set($headlineKey, $value, $headlineDomain);
            }
        }
        if ('' !== $value = $this->extractLangString($inputScreen['label'], $locale, $mainLanguage) ?? '') {
            $value = strtr($value, $parameters);
            $catalog->set($prefix . '.label', $value, $domain);
            if ($headlineKey === $catalog->get($headlineKey, $headlineDomain)) {
                $catalog->set($headlineKey, $value, $headlineDomain);
            }
        }
    }

    /**
     * @param string|array<string, string> $value      The value.
     * @param string                       $locale     The locale.
     * @param string                       $mainLocale The fallback language.
     *
     * @return string|null
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function extractLangString(string|array $value, string $locale, string $mainLocale): ?string
    {
        if (!is_array($value)) {
            return $value;
        }

        $fallback = null;
        foreach ($value as $langCode => $string) {
            if ($locale === $langCode && '' !== $string) {
                return $string;
            }
            if ($mainLocale === $langCode && '' !== $string) {
                $fallback = $string;
            }
        }

        if ('en' === $locale && null === $fallback && (null !== $default = ($value['default'] ?? $value[''] ?? null))) {
            return $default;
        }

        return $fallback;
    }
}
