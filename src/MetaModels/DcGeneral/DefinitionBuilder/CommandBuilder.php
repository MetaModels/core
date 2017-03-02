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
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CopyCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CutCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\SelectCommand;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\BackendIntegration\ViewCombinations;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\DcGeneral\Dca\Builder\Builder;
use MetaModels\DcGeneral\Events\MetaModel\BuildMetaModelOperationsEvent;
use MetaModels\Helper\ToolboxFile;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class builds the commands.
 */
class CommandBuilder
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The view combinations.
     *
     * @var ViewCombinations
     */
    private $viewCombinations;

    /**
     * The container (only set during build phase).
     *
     * @var IMetaModelDataDefinition
     */
    private $container;

    /**
     * The input screen (only set during build phase).
     *
     * @var IInputScreen
     */
    private $inputScreen;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher       The event dispatcher.
     * @param ViewCombinations         $viewCombinations The view combinations.
     */
    public function __construct(EventDispatcherInterface $dispatcher, ViewCombinations $viewCombinations)
    {
        $this->dispatcher       = $dispatcher;
        $this->viewCombinations = $viewCombinations;
    }

    /**
     * Parse and build the backend view definition for the old Contao2 backend view.
     *
     * @param IMetaModelDataDefinition $container   The data container.
     * @param Builder                  $builder     Deprecated - the builder instance to use in events.
     *
     * @throws DcGeneralInvalidArgumentException When the contained view definition is of invalid type.
     *
     * @return void
     */
    public function build(IMetaModelDataDefinition $container, Builder $builder)
    {
        if ($container->hasDefinition(Contao2BackendViewDefinitionInterface::NAME)) {
            $view = $container->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        } else {
            $view = new Contao2BackendViewDefinition();
            $container->setDefinition(Contao2BackendViewDefinitionInterface::NAME, $view);
        }

        if (!$view instanceof Contao2BackendViewDefinitionInterface) {
            throw new DcGeneralInvalidArgumentException(
                'Configured BackendViewDefinition does not implement Contao2BackendViewDefinitionInterface.'
            );
        }

        $this->container   = $container;
        $this->inputScreen = $inputScreen = $this->viewCombinations->getInputScreenDetails($container->getName());
        $this->addEditMultipleCommand($view);
        $this->parseModelOperations($view);
        $this->container   = null;
        $this->inputScreen = null;

        $event = new BuildMetaModelOperationsEvent($inputScreen->getMetaModel(), $container, $inputScreen, $builder);
        $this->dispatcher->dispatch($event::NAME, $event);
    }

    /**
     * Add the select command to the backend view definition.
     *
     * @param Contao2BackendViewDefinitionInterface $view The backend view definition.
     *
     * @return void
     */
    private function addEditMultipleCommand(Contao2BackendViewDefinitionInterface $view)
    {
        $definition = $this->container->getBasicDefinition();
        // No actions allowed. Don't add the select command button.
        if (!$definition->isEditable() && !$definition->isDeletable() && !$definition->isCreatable()) {
            return;
        }

        $commands = $view->getGlobalCommands();
        $command  = new SelectCommand();
        $command
            ->setName('all')
            ->setLabel('MSC.all.0')
            ->setDescription('MSC.all.1');

        $parameters        = $command->getParameters();
        $parameters['act'] = 'select';
        $extra             = $command->getExtra();
        $extra['class']    = 'header_edit_all';

        $commands->addCommand($command);
    }

    /**
     * Parse the defined model scoped operations and populate the definition.
     *
     * @param Contao2BackendViewDefinitionInterface $view The backend view information.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function parseModelOperations(Contao2BackendViewDefinitionInterface $view)
    {
        $collection = $view->getModelCommands();

        $scrOffsetAttributes = ['attributes' => 'onclick="Backend.getScrollOffset();"'];
        $this->createCommand($collection, 'edit', ['act' => 'edit'], 'edit.gif');
        $this->createCommand($collection, 'copy', ['act' => ''], 'copy.gif', $scrOffsetAttributes);
        $this->createCommand($collection, 'cut', ['act' => 'paste', 'mode' => 'cut'], 'cut.gif', $scrOffsetAttributes);
        $this->createCommand(
            $collection,
            'delete',
            ['act' => 'delete'],
            'delete.gif',
            [
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    // FIXME: we need the translation manager here.
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ]
        );
        $this->createCommand($collection, 'show', ['act' => 'show'], 'show.gif');

        if ($this->inputScreen->getMetaModel()->hasVariants()) {
            $this->createCommand(
                $collection,
                'createvariant',
                ['act' => 'createvariant'],
                'system/modules/metamodels/assets/images/icons/variants.png'
            );
        }

        // Check if we have some children.
        foreach ($this->viewCombinations->getParentedInputScreens($this->container->getName()) as $screen) {
            $metaModel = $screen->getMetaModel();
            $tableName = $metaModel->getTableName();
            $caption   = $this->getChildModelCaption($metaModel, $screen);

            $this->createCommand(
                $collection,
                'edit_' . $tableName,
                ['table' => $tableName],
                $this->getBackendIcon($screen->getIcon()),
                [
                    'label'       => $caption[0],
                    'description' => $caption[1],
                    'idparam'     => 'pid'
                ]
            );
        }
    }

    /**
     * Build a command into the the command collection.
     *
     * @param CommandCollectionInterface $collection      The command collection.
     * @param string                     $operationName   The operation name.
     * @param array                      $queryParameters The query parameters for the operation.
     * @param string                     $icon            The icon to use in the backend.
     * @param array                      $extraValues     The extra values for the command.
     *
     * @return void
     */
    private function createCommand(
        CommandCollectionInterface $collection,
        $operationName,
        $queryParameters,
        $icon,
        $extraValues = []
    ) {
        $command    = $this->getCommandInstance($collection, $operationName);
        $parameters = $command->getParameters();
        foreach ($queryParameters as $name => $value) {
            if (!isset($parameters[$name])) {
                $parameters[$name] = $value;
            }
        }

        if (!$command->getLabel()) {
            $command->setLabel($operationName . '.0');
            if (isset($extraValues['label'])) {
                $command->setLabel($extraValues['label']);
            }
        }

        if (!$command->getDescription()) {
            $command->setDescription($operationName . '.1');
            if (isset($extraValues['description'])) {
                $command->setDescription($extraValues['description']);
            }
        }

        $extra         = $command->getExtra();
        $extra['icon'] = $icon;

        foreach ($extraValues as $name => $value) {
            if (!isset($extra[$name])) {
                $extra[$name] = $value;
            }
        }
    }

    /**
     * Retrieve or create a command instance of the given name.
     *
     * @param CommandCollectionInterface $collection    The command collection.
     *
     * @param string                     $operationName The name of the operation.
     *
     * @return CommandInterface
     */
    private function getCommandInstance(CommandCollectionInterface $collection, $operationName)
    {
        if ($collection->hasCommandNamed($operationName)) {
            $command = $collection->getCommandNamed($operationName);
        } else {
            switch ($operationName) {
                case 'cut':
                    $command = new CutCommand();
                    break;

                case 'copy':
                    $command = new CopyCommand();
                    break;

                default:
                    $command = new Command();
            }

            $command->setName($operationName);
            $collection->addCommand($command);
        }

        return $command;
    }

    /**
     * Generate a 16x16 pixel version of the passed image file. If this can not be done, the default image is returned.
     *
     * @param string $icon The name of the image file.
     *
     * @return null|string
     */
    private function getBackendIcon($icon)
    {
        // Determine the image to use.
        if ($icon) {
            $icon = ToolboxFile::convertValueToPath($icon);

            /** @var ResizeImageEvent $event */
            $event = $this->dispatcher->dispatch(
                ContaoEvents::IMAGE_RESIZE,
                new ResizeImageEvent($icon, 16, 16)
            );

            if (file_exists(TL_ROOT . '/' . $event->getResultImage())) {
                return $event->getResultImage();
            }
        }

        return 'system/modules/metamodels/assets/images/icons/metamodels.png';
    }

    /**
     * Create the caption text for the child model.
     *
     * @param IMetaModel   $metaModel The child model.
     * @param IInputScreen $screen    The input screen.
     *
     * @return array
     */
    private function getChildModelCaption($metaModel, $screen)
    {
        $caption = [
            '',
            sprintf(
                $GLOBALS['TL_LANG']['MSC']['metamodel_edit_as_child']['label'],
                $metaModel->getName()
            )
        ];

        foreach ($screen->getBackendCaption() as $languageEntry) {
            if (!empty($languageEntry['label']) && $languageEntry['langcode'] == $GLOBALS['TL_LANGUAGE']) {
                $caption = [
                    $languageEntry['description'],
                    $languageEntry['label']
                ];
            }
        }

        return $caption;
    }
}
