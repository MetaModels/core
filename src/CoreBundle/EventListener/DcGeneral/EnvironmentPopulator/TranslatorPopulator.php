<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\EnvironmentPopulator;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use ContaoCommunityAlliance\Translator\TranslatorChain;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class populates the translator.
 */
class TranslatorPopulator
{
    use MetaModelPopulatorTrait;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The view combination.
     *
     * @var ViewCombination
     */
    private $viewCombination;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher      The event dispatcher.
     * @param ViewCombination          $viewCombination The view combination.
     */
    public function __construct(EventDispatcherInterface $dispatcher, ViewCombination $viewCombination)
    {
        $this->dispatcher      = $dispatcher;
        $this->viewCombination = $viewCombination;
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
        if (!($translator instanceof TranslatorChain)) {
            $translatorChain = new TranslatorChain();
            $translatorChain->add($translator);
            $environment->setTranslator($translatorChain);
        } else {
            $translatorChain = $translator;
        }
        $translatorChain->add($translator = new StaticTranslator());

        // Map the tl_metamodel_item domain over to this domain.
        $this->dispatcher->dispatch(
            new LoadLanguageFileEvent('tl_metamodel_item'),
            ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE
        );

        $definitionName = $environment->getDataDefinition()->getName();
        $this->mapTranslations(
            $GLOBALS['TL_LANG']['tl_metamodel_item'],
            $definitionName,
            $translator
        );
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
            if (is_array($value)) {
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
        $currentLocale = $GLOBALS['TL_LANGUAGE'];

        foreach ($inputScreen['legends'] as $legendName => $legendInfo) {
            // If current language not defined, use the fallback language.
            $translator->setValue(
                $legendName . '_legend',
                $legendInfo['label']['default'],
                $containerName
            );
            foreach ($legendInfo['label'] as $langCode => $label) {
                $translator->setValue(
                    $legendName . '_legend',
                    $label,
                    $containerName,
                    $langCode
                );
                if ($currentLocale === $langCode) {
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
