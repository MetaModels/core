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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CopyCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CutCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\SelectCommand;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use MetaModels\CoreBundle\Assets\IconBuilder;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\DcGeneral\Events\MetaModel\BuildMetaModelOperationsEvent;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class builds the commands.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommandBuilder
{
    use MetaModelDefinitionBuilderTrait;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * The view combinations.
     *
     * @var ViewCombination
     */
    private ViewCombination $viewCombination;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The container (only set during build phase).
     *
     * @var IMetaModelDataDefinition|null
     */
    private IMetaModelDataDefinition|null $container = null;

    /**
     * The icon builder.
     *
     * @var IconBuilder
     */
    private IconBuilder $iconBuilder;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher      The event dispatcher.
     * @param ViewCombination          $viewCombination The view combinations.
     * @param IFactory                 $factory         The MetaModels factory.
     * @param IconBuilder              $iconBuilder     The icon builder.
     * @param TranslatorInterface      $translator      The translator.
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        ViewCombination $viewCombination,
        IFactory $factory,
        IconBuilder $iconBuilder,
        TranslatorInterface $translator
    ) {
        $this->dispatcher      = $dispatcher;
        $this->viewCombination = $viewCombination;
        $this->factory         = $factory;
        $this->iconBuilder     = $iconBuilder;
        $this->translator      = $translator;
    }

    /**
     * Parse and build the backend view definition for the old Contao2 backend view.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @throws DcGeneralInvalidArgumentException When the contained view definition is of invalid type.
     *
     * @return void
     */
    protected function build(IMetaModelDataDefinition $container)
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

        $this->container = $container;
        $inputScreen = $this->viewCombination->getScreen($container->getName());
        if (null === $inputScreen) {
            return;
        }
        $this->addEditMultipleCommand($view);
        $this->parseModelOperations($view);
        $this->container = null;

        if ($this->dispatcher->hasListeners(BuildMetaModelOperationsEvent::NAME)) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Event "' . BuildMetaModelOperationsEvent::NAME . '" is deprecated and will get removed',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $metaModel = $this->factory->getMetaModel($container->getName());
            assert($metaModel instanceof IMetaModel);
            $event = new BuildMetaModelOperationsEvent($metaModel, $container, $inputScreen);
            $this->dispatcher->dispatch($event, $event::NAME);
        }
    }

    /**
     * Add the select command to the backend view definition.
     *
     * @param Contao2BackendViewDefinitionInterface $view The backend view definition.
     *
     * @return void
     */
    private function addEditMultipleCommand(Contao2BackendViewDefinitionInterface $view): void
    {
        assert($this->container instanceof IMetaModelDataDefinition);
        $definition = $this->container->getBasicDefinition();
        // No actions allowed. Don't add the select command button.
        if (!$definition->isEditable() && !$definition->isDeletable() && !$definition->isCreatable()) {
            return;
        }

        $commands = $view->getGlobalCommands();
        $command  = new SelectCommand();
        $command
            ->setName('editAll')
            ->setLabel('editAll.label')
            ->setDescription('editAll.description');

        $parameters           = $command->getParameters();
        $parameters['act']    = 'select';
        $parameters['select'] = 'models';
        $extra                = $command->getExtra();
        $extra['class']       = 'header_edit_all';

        $commands->addCommand($command);
    }

    /**
     * Parse the defined model scoped operations and populate the definition.
     *
     * @param Contao2BackendViewDefinitionInterface $view The backend view information.
     *
     * @return void
     */
    private function parseModelOperations(Contao2BackendViewDefinitionInterface $view): void
    {
        assert($this->container instanceof IMetaModelDataDefinition);
        $collection = $view->getModelCommands();

        $scrOffsetAttributes = ['attributes' => 'onclick="Backend.getScrollOffset();"'];
        $this->createCommand($collection, 'edit', ['act' => 'edit'], 'edit.svg');
        $this->createCommand($collection, 'copy', ['act' => 'copy'], 'copy.svg', $scrOffsetAttributes);
        $this->createCommand($collection, 'cut', ['act' => 'cut'], 'cut.svg', $scrOffsetAttributes);
        $this->createCommand(
            $collection,
            'delete',
            ['act' => 'delete'],
            'delete.svg',
            [
                'attributes' =>
                    'onclick="if (!confirm(this.dataset.msgConfirm)) return false; Backend.getScrollOffset();"',
            ]
        );
        $this->createCommand($collection, 'show', ['act' => 'show'], 'show.svg');

        $metaModel = $this->factory->getMetaModel($this->container->getName());
        assert($metaModel instanceof IMetaModel);
        if ($metaModel->hasVariants()) {
            $this->createCommand(
                $collection,
                'createvariant',
                ['act' => 'createvariant'],
                '/bundles/metamodelscore/images/icons/variants.png'
            );
        }

        // Check if we have some children.
        foreach ($this->viewCombination->getChildrenOf($this->container->getName()) as $tableName => $screen) {
            $screenId = (string) ($screen['meta']['id'] ?? '');
            assert('' !== $screenId);
            $this->createCommand(
                $collection,
                'edit_' . $tableName,
                ['table' => $tableName],
                $this->iconBuilder->getBackendIcon($screen['meta']['backendicon']),
                [
                    'label'       => 'metamodel_edit_as_child.' . $tableName . '.' . $screenId . '.label',
                    'description' => 'metamodel_edit_as_child.' . $tableName . '.' . $screenId . '.description',
                    'idparam'     => 'pid'
                ]
            );
        }
    }

    /**
     * Build a command into the command collection.
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
        string $operationName,
        array $queryParameters,
        string $icon,
        array $extraValues = []
    ) {
        $command    = $this->getCommandInstance($collection, $operationName);
        $parameters = $command->getParameters();
        foreach ($queryParameters as $name => $value) {
            if (!isset($parameters[$name])) {
                $parameters[$name] = $value;
            }
        }

        if (!$command->getLabel()) {
            $command->setLabel($operationName . '.label');
            if (isset($extraValues['label'])) {
                $command->setLabel($extraValues['label']);
            }
        }

        if (!$command->getDescription()) {
            $command->setDescription($operationName . '.description');
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
     * @param string                     $operationName The name of the operation.
     *
     * @return CommandInterface
     */
    private function getCommandInstance(CommandCollectionInterface $collection, string $operationName)
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
}
