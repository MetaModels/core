<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2016 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\ContaoIntegration;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\LoadDataContainerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PreCreateDcGeneralEvent;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use MetaModels\Dca\MetaModelDcaBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\BasicDefinitionBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\CommandBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\Contao2BackendViewDefinitionBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\DataProviderBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\MetaModelDefinitionBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\PaletteBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\PanelBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\PropertyDefinitionBuilder;
use MetaModels\DcGeneral\Events\MetaModel\RenderItem;
use MetaModels\DcGeneral\Populator\AttributePopulator;
use MetaModels\DcGeneral\Populator\DataProviderPopulator;
use MetaModels\DcGeneral\Populator\TranslatorPopulator;
use MetaModels\Helper\LoadDataContainerHookListener;
use MetaModels\Helper\ViewCombinations;
use MetaModels\IMetaModelsServiceContainer;

/**
 * This class registers the data container loaders.
 *
 * It is extended by the frontend and backend boot classes.
 */
abstract class Boot
{
    /**
     * Register the loadDataContainer HOOK for the given table name to create the DcGeneral etc.
     *
     * @param string                      $tableName The name of the table to be loaded.
     *
     * @param IMetaModelsServiceContainer $container The MetaModels service container.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function attachLoadDataContainerHook($tableName, $container)
    {
        LoadDataContainerHookListener::attachFor(
            $tableName,
            function ($tableName) use ($container) {
                $dispatcher = $container->getEventDispatcher();
                $event      = new LoadDataContainerEvent('tl_metamodel_item');
                $dispatcher->dispatch(
                    ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE,
                    new LoadLanguageFileEvent('tl_metamodel_item')
                );
                $dispatcher->dispatch(ContaoEvents::CONTROLLER_LOAD_DATA_CONTAINER, $event);

                if (!isset($GLOBALS['TL_DCA'][$tableName])) {
                    $GLOBALS['TL_DCA'][$tableName] = array();
                }

                $GLOBALS['TL_DCA'][$tableName] = array_replace_recursive(
                    (array) $GLOBALS['TL_DCA']['tl_metamodel_item'],
                    (array) $GLOBALS['TL_DCA'][$tableName]
                );
            }
        );
    }

    /**
     * Boot the system and register dca loader etc.
     *
     * @param IMetaModelsServiceContainer $container        The service container.
     *
     * @param ViewCombinations            $viewCombinations The view combinations to use.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function performBoot(IMetaModelsServiceContainer $container, ViewCombinations $viewCombinations)
    {
        // Prepare lazy loading of the data containers.
        foreach ($viewCombinations->getParentedInputScreenNames() as $metaModelName) {
            $parent = $viewCombinations->getParentOf($metaModelName);
            if (substr($parent, 0, 3) === 'mm_') {
                continue;
            }
            LoadDataContainerHookListener::attachFor(
                $parent,
                function () use ($metaModelName, $viewCombinations, $container) {
                    $inputScreen = $viewCombinations->getInputScreenDetails($metaModelName);
                    $builder     = new MetaModelDcaBuilder($container);
                    $builder->injectOperationButton($inputScreen);
                }
            );
        }
        $translator = new StaticTranslator();
        $dispatcher = $container->getEventDispatcher();
        $dispatcher->addListener(
            BuildDataDefinitionEvent::NAME,
            [new MetaModelDefinitionBuilder($viewCombinations), 'handle']
        );
        $dispatcher->addListener(
            BuildDataDefinitionEvent::NAME,
            [new PropertyDefinitionBuilder($dispatcher, $viewCombinations), 'handle']
        );
        $dispatcher->addListener(
            BuildDataDefinitionEvent::NAME,
            [new BasicDefinitionBuilder($viewCombinations), 'handle']
        );
        $dispatcher->addListener(
            BuildDataDefinitionEvent::NAME,
            [new DataProviderBuilder($viewCombinations, $container->getFactory()), 'handle']
        );
        $dispatcher->addListener(
            BuildDataDefinitionEvent::NAME,
            [new Contao2BackendViewDefinitionBuilder(
                $viewCombinations,
                $dispatcher,
                $container->getRenderSettingFactory()
            ), 'handle']
        );
        $dispatcher->addListener(
            BuildDataDefinitionEvent::NAME,
            [new CommandBuilder($dispatcher, $viewCombinations), 'handle']
        );
        $dispatcher->addListener(
            BuildDataDefinitionEvent::NAME,
            [new PanelBuilder($viewCombinations), 'handle']
        );
        $dispatcher->addListener(
            BuildDataDefinitionEvent::NAME,
            [new PaletteBuilder($viewCombinations, $translator), 'handle']
        );
        RenderItem::register($dispatcher);

        foreach ($container->getFactory()->collectNames() as $metaModelName) {
            $this->attachLoadDataContainerHook($metaModelName, $container);

            $dispatcher->addListener(
                PreCreateDcGeneralEvent::NAME,
                function (PreCreateDcGeneralEvent $event) use ($metaModelName, $viewCombinations, $container, $translator) {
                    $factory = $event->getFactory();
                    $name    = $factory->getContainerName();
                    if ($name !== $metaModelName) {
                        return;
                    }

                    $inputScreen = $viewCombinations->getInputScreenDetails($metaModelName);

                    $factory->setContainerClassName('MetaModels\DcGeneral\DataDefinition\MetaModelDataDefinition');

                    $dispatcher = $container->getEventDispatcher();

                    $dispatcher->addListener(
                        PopulateEnvironmentEvent::NAME,
                        function (PopulateEnvironmentEvent $event) use (
                            $metaModelName,
                            $inputScreen,
                            $container,
                            $translator
                        ) {
                            if ($event->getEnvironment()->getDataDefinition()->getName() !== $metaModelName) {
                                return;
                            }

                            $environment = $event->getEnvironment();

                            $populator = new TranslatorPopulator($container, $translator);
                            $populator->populate($environment);

                            $populator = new AttributePopulator($inputScreen);
                            $populator->populate($environment);

                            $populator = new DataProviderPopulator($container);
                            $populator->populate($environment);
                            unset($populator);

                            $GLOBALS['TL_CSS'][] = 'system/modules/metamodels/assets/css/style.css';
                        }
                    );
                }
            );
        }
    }
}
