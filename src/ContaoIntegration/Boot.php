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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\ContaoIntegration;

use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PreCreateDcGeneralEvent;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use MetaModels\Dca\MetaModelDcaBuilder;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
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

        $dispatcher->addListener(
            PopulateEnvironmentEvent::NAME,
            [new TranslatorPopulator($dispatcher, $translator), 'handle']
        );
        $dispatcher->addListener(
            PopulateEnvironmentEvent::NAME,
            [new AttributePopulator($dispatcher, $viewCombinations), 'handle']
        );
        $dispatcher->addListener(
            PopulateEnvironmentEvent::NAME,
            [new DataProviderPopulator($container), 'handle']
        );
        $dispatcher->addListener(
            PopulateEnvironmentEvent::NAME,
            function (PopulateEnvironmentEvent $event) {
                if ($event->getEnvironment()->getDataDefinition() instanceof IMetaModelDataDefinition) {
                    $GLOBALS['TL_CSS'][] = 'bundles/metamodelscore/css/style.css';
                }
            }
        );

        $names = $container->getFactory()->collectNames();
        $dispatcher->addListener(
            PreCreateDcGeneralEvent::NAME,
            function (PreCreateDcGeneralEvent $event) use ($names) {
                $factory = $event->getFactory();
                if (!in_array($factory->getContainerName(), $names)) {
                    return;
                }
                $factory->setContainerClassName('MetaModels\DcGeneral\DataDefinition\MetaModelDataDefinition');
            }
        );
    }
}
