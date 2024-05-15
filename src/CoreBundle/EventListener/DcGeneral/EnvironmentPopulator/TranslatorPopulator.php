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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\EnvironmentPopulator;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use ContaoCommunityAlliance\Translator\SymfonyTranslatorBridge;
use ContaoCommunityAlliance\Translator\TranslatorChain;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\Helper\LocaleUtil;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class populates the translator.
 */
class TranslatorPopulator
{
    use MetaModelPopulatorTrait;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher      The event dispatcher.
     * @param ViewCombination          $viewCombination The view combination.
     * @param SymfonyTranslator        $translator      The translator.
     */
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private ViewCombination $viewCombination,
        private SymfonyTranslator $translator
    ) {
    }

    /**
     * Populate the environment.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function populate(EnvironmentInterface $environment)
    {
        $translator = $environment->getTranslator();
        assert($translator instanceof TranslatorInterface);

        if (!($translator instanceof TranslatorChain)) {
            $translatorChain = new TranslatorChain();
            $translatorChain->add($translator);
            $environment->setTranslator($translatorChain);
        } else {
            $translatorChain = $translator;
        }
        $translatorChain->add(new SymfonyTranslatorBridge($this->translator));
        $translatorChain->add($translator = new StaticTranslator());
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);
        $definitionName = $dataDefinition->getName();

        if (null === $inputScreen = $this->viewCombination->getScreen($definitionName)) {
            return;
        }

        $this->addInputScreenTranslations(
            $translator,
            $inputScreen,
            $definitionName
        );
    }

    /**
     * Map all translation values from the given array to the given destination domain using the optional base key.
     *
     * @param array            $array      The array holding the translation values.
     * @param string           $domain     The target domain.
     * @param StaticTranslator $translator The translator.
     * @param string           $baseKey    The base key to prepend the values of the array with.
     *
     * @return void
     */
    private function mapTranslations($array, $domain, StaticTranslator $translator, $baseKey = '')
    {
        foreach ($array as $key => $value) {
            $newKey = ($baseKey ? $baseKey . '.' : '') . $key;
            if (\is_array($value)) {
                $this->mapTranslations($value, $domain, $translator, $newKey);
            } else {
                $translator->setValue($newKey, $value, $domain);
            }
        }
    }

    /**
     * Add the translations for the input screen.
     *
     * @param StaticTranslator $translator    The translator.
     * @param array            $inputScreen   The input screen.
     * @param string           $containerName The container name.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function addInputScreenTranslations(StaticTranslator $translator, $inputScreen, $containerName)
    {
        // Either 2 or 5 char long language code.
        // @deprecated usage of TL_LANGUAGE - remove for Contao 5.0.
        $currentLocale = LocaleUtil::formatAsLocale($GLOBALS['TL_LANGUAGE']);
        // Either 2 char language code or null.
        $shortLocale = (\str_contains($currentLocale, '_'))
            ? \explode('_', $currentLocale, 2)[0]
            : null;

        foreach ($inputScreen['legends'] as $legendName => $legendInfo) {
            // If current language not defined, use the fallback language.
            $translator->setValue(
                $legendName . '_legend',
                $legendInfo['label']['default'],
                $containerName
            );

            $fallbackLocales = [$currentLocale];
            if ((null !== $shortLocale) && !\array_key_exists($currentLocale, $legendInfo['label'])) {
                $fallbackLocales[] = $shortLocale;
            }

            foreach ($legendInfo['label'] as $langCode => $label) {
                // Default is already handled above, do not overwrite!
                if ($langCode === 'default') {
                    continue;
                }

                $translator->setValue(
                    $legendName . '_legend',
                    $label,
                    $containerName,
                    $langCode
                );

                if (\in_array($langCode, $fallbackLocales)) {
                    $translator->setValue(
                        $legendName . '_legend',
                        $label,
                        $containerName
                    );
                }
            }
        }
    }
}
