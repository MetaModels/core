<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\LoadDataContainerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PreCreateDcGeneralEvent;
use MetaModels\Dca\MetaModelDcaBuilder;
use MetaModels\DcGeneral\Dca\Builder\Builder;
use MetaModels\DcGeneral\Events\Subscriber;
use MetaModels\Events\MetaModelsBootEvent;
use MetaModels\Helper\LoadDataContainerHookListener;
use MetaModels\IMetaModelsServiceContainer;

/**
 * This class is used in the backend to build the menu etc.
 */
class Boot
{
    /**
     * When we are within Contao >= 3.1, we have to override the file picker class.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function overrideFilePicker()
    {
        if (\Environment::get('scriptName') == (TL_PATH . '/contao/file.php')
            && \Input::get('mmfilepicker')
        ) {
            $GLOBALS['BE_FFL']['fileSelector'] = 'MetaModels\Widgets\FileSelectorWidget';
        }
    }

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
     * Boot the system in the backend.
     *
     * @param MetaModelsBootEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function perform(MetaModelsBootEvent $event)
    {
        $container = $event->getServiceContainer();

        $viewCombinations = new ViewCombinations($container, $GLOBALS['container']['user']);
        $container->setService($viewCombinations, 'metamodels-view-combinations');

        $menuBuilder = new BackendModuleBuilder($container, $viewCombinations);
        $menuBuilder->export();

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

        $dispatcher = $container->getEventDispatcher();
        foreach ($container->getFactory()->collectNames() as $metaModelName) {
            $this->attachLoadDataContainerHook($metaModelName, $container);

            $dispatcher->addListener(
                PreCreateDcGeneralEvent::NAME,
                function (PreCreateDcGeneralEvent $event) use ($metaModelName, $viewCombinations, $container) {
                    $factory = $event->getFactory();
                    $name    = $factory->getContainerName();
                    if ($name !== $metaModelName) {
                        return;
                    }

                    $inputScreen = $viewCombinations->getInputScreenDetails($metaModelName);

                    $factory->setContainerClassName('MetaModels\DcGeneral\DataDefinition\MetaModelDataDefinition');

                    $dispatcher = $container->getEventDispatcher();
                    $generator  = new Builder($container, $inputScreen);

                    $dispatcher->addListener(
                        BuildDataDefinitionEvent::NAME,
                        function (BuildDataDefinitionEvent $event) use ($metaModelName, $generator) {
                            if ($event->getContainer()->getName() !== $metaModelName) {
                                return;
                            }
                            $generator->build($event);
                        },
                        $generator::PRIORITY
                    );
                    $dispatcher->addListener(
                        PopulateEnvironmentEvent::NAME,
                        function (PopulateEnvironmentEvent $event) use ($metaModelName, $generator) {
                            if ($event->getEnvironment()->getDataDefinition()->getName() !== $metaModelName) {
                                return;
                            }
                            $generator->populate($event);
                            $GLOBALS['TL_CSS'][] = 'system/modules/metamodels/assets/css/style.css';
                        },
                        $generator::PRIORITY
                    );
                }
            );
        }

        // Register the global subscriber.
        new Subscriber($container);

        $this->overrideFilePicker();
    }
}
