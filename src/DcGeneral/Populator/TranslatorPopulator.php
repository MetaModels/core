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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Populator;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use ContaoCommunityAlliance\Translator\TranslatorChain;
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
     * The translator instance this builder adds values to.
     *
     * @var StaticTranslator
     */
    private $translator;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     * @param StaticTranslator         $translator The translator.
     */
    public function __construct(EventDispatcherInterface $dispatcher, StaticTranslator $translator)
    {
        $this->dispatcher = $dispatcher;
        $this->translator = $translator;
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
        $translatorChain->add($this->translator);

        // Map the tl_metamodel_item domain over to this domain.
        $this->dispatcher->dispatch(
            ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE,
            new LoadLanguageFileEvent('tl_metamodel_item')
        );

        $this->mapTranslations(
            $GLOBALS['TL_LANG']['tl_metamodel_item'],
            $environment->getDataDefinition()->getName()
        );
    }

    /**
     * Map all translation values from the given array to the given destination domain using the optional base key.
     *
     * @param array  $array   The array holding the translation values.
     *
     * @param string $domain  The target domain.
     *
     * @param string $baseKey The base key to prepend the values of the array with.
     *
     * @return void
     */
    private function mapTranslations($array, $domain, $baseKey = '')
    {
        foreach ($array as $key => $value) {
            $newKey = ($baseKey ? $baseKey . '.' : '') . $key;
            if (is_array($value)) {
                $this->mapTranslations($value, $domain, $newKey);
            } else {
                $this->translator->setValue($newKey, $value, $domain);
            }
        }
    }
}
