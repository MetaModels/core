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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\LoadDataContainerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PreCreateDcGeneralEvent;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\Dca\MetaModelDcaBuilder;
use MetaModels\DcGeneral\Dca\Builder\Builder;
use MetaModels\DcGeneral\Events\Subscriber;
use MetaModels\Events\MetaModelsBootEvent;
use MetaModels\Helper\LoadDataContainerHookListener;
use MetaModels\Helper\ToolboxFile;
use MetaModels\IMetaModelsServiceContainer;

/**
 * This class is used in the backend to build the menu etc.
 */
class Boot
{
    /**
     * Handle stand alone integration in the backend.
     *
     * @param IMetaModelsServiceContainer $container   The service container.
     *
     * @param IInputScreen                $inputScreen The input screen containing the information.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function addModuleToBackendMenu($container, $inputScreen)
    {
        $metaModel  = $inputScreen->getMetaModel();
        $dispatcher = $container->getEventDispatcher();

        $moduleName = 'metamodel_' . $metaModel->getTableName();

        $tableCaption = $metaModel->getName();

        $icon = ToolboxFile::convertValueToPath($inputScreen->getIcon());
        // Determine image to use.
        if ($icon && file_exists(TL_ROOT . '/' . $icon)) {
            $event = new ResizeImageEvent($icon, 16, 16);

            $dispatcher->dispatch(ContaoEvents::IMAGE_RESIZE, $event);
            $icon = $event->getResultImage();
        } else {
            $icon = 'system/modules/metamodels/assets/images/icons/metamodels.png';
        }

        $section = $inputScreen->getBackendSection();

        if (!$section) {
            $section = 'metamodels';
        }

        $GLOBALS['BE_MOD'][$section][$moduleName] = array
        (
            'tables'   => array($metaModel->getTableName()),
            'icon'     => $icon,
            'callback' => 'MetaModels\BackendIntegration\Module'
        );

        $caption = array($tableCaption);
        foreach ($inputScreen->getBackendCaption() as $languageEntry) {
            if ($languageEntry['langcode'] == 'en') {
                $caption = array($languageEntry['label'], $languageEntry['description']);
            }

            if (!empty($languageEntry['label']) && ($languageEntry['langcode'] == $GLOBALS['TL_LANGUAGE'])) {
                $caption = array($languageEntry['label'], $languageEntry['description']);
                break;
            }
        }

        $GLOBALS['TL_LANG']['MOD'][$moduleName] = $caption;
    }

    /**
     * Retrieve the table names from a list of input screens.
     *
     * @param IInputScreen[] $inputScreens The input screens containing the information.
     *
     * @return string[]
     */
    private function getTableNamesFromInputScreens($inputScreens)
    {
        $parentTables = array();
        foreach ($inputScreens as $screen) {
            $parentTable = $screen->getParentTable();

            $parentTables[$parentTable][] = $screen->getMetaModel()->getTableName();
        }

        return $parentTables;
    }

    /**
     * Inject all meta models into their corresponding parent tables.
     *
     * @param IInputScreen[] $inputScreens The input screens to be added.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function addChildTablesToBackendModules($inputScreens)
    {
        $parentTables = $this->getTableNamesFromInputScreens($inputScreens);
        $lastCount    = count($parentTables);
        // Loop until all tables are injected or until there was no injection during one run.
        // This is important, as we might have models that are child of another model.
        while ($parentTables) {
            foreach ($parentTables as $parentTable => $childTables) {
                foreach ($GLOBALS['BE_MOD'] as $groupName => $modules) {
                    foreach ($modules as $moduleName => $moduleConfiguration) {
                        if (
                            isset($moduleConfiguration['tables'])
                            && in_array($parentTable, $moduleConfiguration['tables'])
                        ) {
                            $GLOBALS['BE_MOD'][$groupName][$moduleName]['tables'] = array_merge(
                                $GLOBALS['BE_MOD'][$groupName][$moduleName]['tables'],
                                $childTables
                            );
                            unset($parentTables[$parentTable]);

                            break;
                        }
                    }
                }
            }
            if (count($parentTables) == $lastCount) {
                break;
            }
            $lastCount = count($parentTables);
        }
    }

    /**
     * Inject MetaModels in the backend menu.
     *
     * @param IMetaModelsServiceContainer $container        The service container.
     *
     * @param ViewCombinations            $viewCombinations The view combinations.
     *
     * @return void
     */
    private function buildBackendMenu($container, $viewCombinations)
    {
        // TODO: we can cache all this here and speed up things big time.
        foreach ($viewCombinations->getStandaloneInputScreens() as $inputScreen) {
            $this->addModuleToBackendMenu($container, $inputScreen);
        }

        $this->addChildTablesToBackendModules($viewCombinations->getParentedInputScreens());
    }

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
        if (version_compare(VERSION, '3.1', '>=')
            && \Environment::get('scriptName') == (TL_PATH . '/contao/file.php')
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

        if (!$container->getDatabase()->tableExists('tl_metamodel', null)) {
            return;
        }

        $viewCombinations = new ViewCombinations($container, $GLOBALS['container']['user'], TL_ROOT . '/system/cache');
        $container->setService($viewCombinations, 'metamodels-view-combinations');

        $this->buildBackendMenu($container, $viewCombinations);

        // Prepare lazy loading of the data containers.
        foreach ($viewCombinations->getParentedInputScreens() as $inputScreen) {
            $this->attachLoadDataContainerHook($inputScreen->getMetaModel()->getTableName(), $container);

            LoadDataContainerHookListener::attachFor(
                $inputScreen->getParentTable(),
                function () use ($inputScreen, $container) {
                    $builder = new MetaModelDcaBuilder($container);
                    $builder->injectOperationButton($inputScreen);
                }
            );
        }

        $dispatcher = $container->getEventDispatcher();
        foreach ($container->getFactory()->collectNames() as $metaModelName) {
            $inputScreen = $viewCombinations->getInputScreenDetails($metaModelName);
            $this->attachLoadDataContainerHook($inputScreen->getMetaModel()->getTableName(), $container);

            $dispatcher->addListener(
                PreCreateDcGeneralEvent::NAME,
                function (PreCreateDcGeneralEvent $event) use ($metaModelName, $inputScreen, $container) {
                    $factory = $event->getFactory();
                    $name    = $factory->getContainerName();

                    if ($name !== $metaModelName) {
                        return;
                    }
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
