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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteRootButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\IFactory;

/**
 * This class handles the paste into and after button activation and deactivation for all MetaModels being edited.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PasteButtonListener
{
    /**
     * The factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The current environment.
     *
     * @var EnvironmentInterface|null
     */
    private EnvironmentInterface|null $environment = null;

    /**
     * The current data provider.
     *
     * @var DataProviderInterface|null
     */
    private DataProviderInterface|null $provider = null;

    /**
     * The name of current data provider.
     *
     * @var string|null
     */
    private string|null $providerName = null;

    /**
     * The model where we have to check if is it a paste into or paste after.
     *
     * @var ModelInterface|null
     */
    private ModelInterface|null $currentModel = null;

    /**
     * Get determinator if there exists a circular reference.
     *
     * This flag determines if there exists a circular reference between the item currently in the clipboard and the
     * current model. A circular reference is of relevance when performing a cut and paste operation for example.
     *
     * @var boolean|null
     */
    private bool|null $circularReference = null;

    /**
     * Disable the paste after.
     *
     * @var bool
     */
    private bool $disablePA = true;

    /**
     * Disable the paste into.
     *
     * @var bool
     */
    private bool $disablePI = true;

    /**
     * Create a new instance.
     *
     * @param IFactory $factory The factory.
     */
    public function __construct(IFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Handle the paste into and after buttons.
     *
     * @param GetPasteButtonEvent $event The event.
     *
     * @return void
     *
     * @throws \RuntimeException When more than one model is contained within the clipboard.
     */
    public function handle(GetPasteButtonEvent $event)
    {
        $this->circularReference = $event->isCircularReference();
        $this->environment       = $event->getEnvironment();
        $provider                = $this->environment->getDataProvider();
        assert($provider instanceof DataProviderInterface);
        $this->provider     = $provider;
        $this->providerName = $this->provider->getEmptyModel()->getProviderName();
        $clipboard          = $this->environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);
        $currentModel = $event->getModel();
        assert($currentModel instanceof ModelInterface);
        $this->currentModel = $currentModel;
        $this->disablePI    = true;
        $this->disablePA    = true;

        // Only run for a MetaModels and if both values already disabled return here.
        if (
            (!\str_starts_with($this->providerName, 'mm_'))
            || ($event->isPasteIntoDisabled() && $event->isPasteAfterDisabled())
        ) {
            return;
        }

        $this->checkForAction($clipboard, 'copy');
        $this->checkForAction($clipboard, 'create');
        $this->checkForAction($clipboard, 'cut');

        $event
            ->setPasteAfterDisabled($this->disablePA)
            ->setPasteIntoDisabled($this->disablePI);
    }

    /**
     * Handle the paste into and after buttons.
     *
     * @param GetPasteRootButtonEvent $event The event.
     *
     * @return void
     *
     * @throws \RuntimeException When more than one model is contained within the clipboard.
     */
    public function handleRoot(GetPasteRootButtonEvent $event)
    {
        $this->environment = $event->getEnvironment();
        $provider          = $this->environment->getDataProvider();
        assert($provider instanceof DataProviderInterface);
        $this->provider     = $provider;
        $this->providerName = $this->provider->getEmptyModel()->getProviderName();
        $clipboard          = $this->environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);
        $this->currentModel = null;
        $this->disablePI    = false;

        // Only run for a MetaModels.
        if ((!\str_starts_with($this->providerName, 'mm_')) || $event->isPasteDisabled()) {
            return;
        }

        $this->checkForAction($clipboard, 'copy');
        $this->checkForAction($clipboard, 'create');
        $this->checkForAction($clipboard, 'cut');

        $event->setPasteDisabled($this->disablePI);
    }

    /**
     * Find an item by its id.
     *
     * @param mixed $modelId The id to find.
     *
     * @return ModelInterface|null
     */
    private function getModelById(mixed $modelId): ?ModelInterface
    {
        if ($modelId === null) {
            return null;
        }

        $environment = $this->environment;
        assert($environment instanceof EnvironmentInterface);
        $dataProvider = $environment->getDataProvider();
        assert($dataProvider instanceof DataProviderInterface);

        $config = $dataProvider->getEmptyConfig()->setId($modelId);

        return $dataProvider->fetch($config);
    }

    /**
     * Determines if this MetaModel instance is subject to variant handling.
     *
     * @return bool true if variants are handled, false otherwise.
     *
     * @throws \RuntimeException When the MetaModel can not be loaded.
     */
    private function hasVariants(): bool
    {
        if ($this->providerName === null) {
            throw new \RuntimeException('No MetaModel name given');
        }
        $metaModel = $this->factory->getMetaModel($this->providerName);

        if ($metaModel === null) {
            throw new \RuntimeException(\sprintf('Could not find a MetaModel with the name %s', $this->providerName));
        }

        return $metaModel->hasVariants();
    }

    /**
     * Check the buttons based on the action.
     *
     * @param ClipboardInterface $clipboard The clipboard.
     * @param string             $action    The action to be checked.
     *
     * @return void
     */
    private function checkForAction(ClipboardInterface $clipboard, string $action): void
    {
        // Make a filter for the given action.
        $filter = new Filter();
        $filter->andActionIs($action);
        $items = $clipboard->fetch($filter);

        foreach ($items as $item) {
            // Check the context.
            $itemProviderName = $item->getDataProviderName();
            $modelId          = $item->getModelId();

            if ($this->providerName !== $itemProviderName) {
                continue;
            }

            if (!$modelId) {
                $this->checkEmpty($action);
                continue;
            }

            $containedModel = $this->getModelById($modelId->getId());
            if ($this->currentModel === null) {
                $this->checkForRoot($containedModel, $action);
            } elseif ($containedModel) {
                $this->checkForModel($containedModel, $action);
            } else {
                $this->checkEmpty($action);
            }
        }
    }

    /**
     * Check the PA and PI without a contained model.
     *
     * @param string $action The action to be checked.
     *
     * @return void
     */
    private function checkEmpty(string $action): void
    {
        if ($this->hasVariants() && $this->currentModel !== null) {
            $this->disablePA = false;
        } elseif ($action === 'create') {
            $this->disablePA = false;
            $this->disablePI = false;
        }
    }

    /**
     * Check the PI for the root element.
     *
     * @param ModelInterface|null $containedModel The model with all data.
     * @param string              $action         The action to be checked.
     *
     * @return void
     */
    private function checkForRoot(?ModelInterface $containedModel, string $action): void
    {
        if (
            $action === 'cut'
            && null !== $containedModel
            && $this->hasVariants()
            && $containedModel->getProperty('varbase') === ''
        ) {
            $this->disablePI = true;
        }
    }

    /**
     * Check the PA and PI with a model.
     *
     * @param ModelInterface $containedModel The model with all data.
     * @param string         $action         The action to be checked.
     *
     * @return void
     */
    private function checkForModel(ModelInterface $containedModel, string $action): void
    {
        if (!$this->circularReference) {
            if ($this->hasVariants()) {
                $this->checkModelWithVariants($containedModel);
            }
            $this->checkModelWithoutVariants($containedModel, $action);

            return;
        }

        if ($this->currentModel === null) {
            if ($containedModel->getProperty('varbase') === '') {
                $this->disablePA = true;
            }

            return;
        }
        $this->disablePA = false;
        // The following rules apply:
        // 1. Variant bases must not get pasted into anything.
        // 2. If we are not in create mode, disable the paste into for the item itself.
        $this->disablePI =
            ($this->hasVariants() && $containedModel->getProperty('varbase') === '1')
            || ($action !== 'create' && $containedModel->getId() === $this->currentModel->getId());
    }

    /**
     * Check the PA and PI with a model and variant support.
     *
     * @param ModelInterface $containedModel The model to check.
     *
     * @return void
     */
    private function checkModelWithVariants(ModelInterface $containedModel): void
    {
        // Item and variant support.
        $isVarbase        = (bool) $containedModel->getProperty('varbase');
        $vargroup         = $containedModel->getProperty('vargroup');
        $isCurrentVarbase = (bool) $this->currentModel?->getProperty('varbase');
        $currentVargroup  = $this->currentModel?->getProperty('vargroup');

        if ($isVarbase && !$this->circularReference && $isCurrentVarbase) {
            // Insert new items only after bases.
            // Insert a varbase after any other varbase, for sorting.
            $this->disablePA = false;
        } elseif (!$isVarbase && !$isCurrentVarbase && $vargroup === $currentVargroup) {
            // Move items in their vargroup and only there.
            $this->disablePA = false;
        }

        $this->disablePI = !$isCurrentVarbase || $isVarbase;
    }

    /**
     * Check the PA and PI with a model and a normal flat build.
     *
     * @param ModelInterface $containedModel The model to check.
     * @param string         $action         The action to be checked.
     *
     * @return void
     */
    private function checkModelWithoutVariants(ModelInterface $containedModel, string $action): void
    {
        // A copy can always be inserted after itself or into itself.
        if ('copy' === $action) {
            $this->disablePA = false;
            $this->disablePI = false;

            return;
        }

        $environment = $this->environment;
        assert($environment instanceof EnvironmentInterface);
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $parentDefinition = null !== $dataDefinition->getBasicDefinition()->getParentDataProvider();
        $currentModelId   = $this->currentModel?->getId();
        $currentModelPid  = $this->currentModel?->getProperty('pid');
        $this->disablePA  = ($currentModelId === $containedModel->getId())
            || ($parentDefinition && $currentModelPid === $containedModel->getProperty('pid'));
        $this->disablePI  = ($this->circularReference)
            || ($currentModelId === $containedModel->getId())
            || ($parentDefinition && $currentModelPid === $containedModel->getId());
    }
}
