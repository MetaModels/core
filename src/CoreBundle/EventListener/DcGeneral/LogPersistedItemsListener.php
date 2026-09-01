<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\EventListener\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use MetaModels\CoreBundle\Backend\ItemLabelRenderer;
use MetaModels\IFactory;
use MetaModels\ViewCombination\ViewCombination;
use Psr\Log\LoggerInterface;

/**
 * Logs creating, duplicating and deleting a MetaModel item to the Contao system log (tl_log), the
 * way dc-general's own generic LogPersistedModelsListener does for every other DC_General table -
 * except with the item named the same way the edit mask headline and breadcrumb already do
 * (ItemLabelRenderer, the input screen's "subheadline" pattern), instead of a bare table+id. See
 * ".claude/dcg-systemlog.md".
 *
 * MetaModels items opt out of the generic listener entirely (DataProviderBuilder sets
 * setLoggingEnabled(false) on their provider information) so that a create/duplicate/delete is not
 * logged twice under two different wordings. The three MetaModels configuration tables
 * (tl_metamodel_rendersettings and friends) are unaffected and keep using the generic listener - a
 * bare table+id is all that is meaningful for those anyway.
 *
 * Deliberately not "edit", for the same reason as the generic listener: Contao's own tables do not
 * log edits either, the version history is what covers that.
 */
final class LogPersistedItemsListener
{
    public function __construct(
        private readonly IFactory $factory,
        private readonly ViewCombination $viewCombination,
        private readonly ItemLabelRenderer $labelRenderer,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Log the creation of a new item.
     *
     * @param PostPersistModelEvent $event The event.
     *
     * @return void
     */
    public function onPersist(PostPersistModelEvent $event): void
    {
        // Edits fire this same event with the previously stored data as original model. A create
        // is not signalled by a null original model - CreateHandler passes an empty one
        // (getEmptyModel()), never a literal null - it has no id yet, which is what actually tells
        // the two apart.
        $originalModel = $event->getOriginalModel();
        if (null !== $originalModel && null !== $originalModel->getId()) {
            return;
        }

        $this->log(
            $event->getModel(),
            fn (string $label): string => \sprintf('A new entry "%s" has been created', $label)
        );
    }

    /**
     * Log the creation of an item by duplicating another one.
     *
     * @param PostDuplicateModelEvent $event The event.
     *
     * @return void
     */
    public function onDuplicate(PostDuplicateModelEvent $event): void
    {
        $sourceLabel = $this->describe($event->getSourceModel());

        $this->log(
            $event->getModel(),
            fn (string $label): string => \sprintf(
                'A new entry "%s" has been created by duplicating record "%s"',
                $label,
                $sourceLabel
            )
        );
    }

    /**
     * Log the deletion of an item.
     *
     * @param PostDeleteModelEvent $event The event.
     *
     * @return void
     */
    public function onDelete(PostDeleteModelEvent $event): void
    {
        $this->log($event->getModel(), fn (string $label): string => \sprintf('Deleted entry "%s"', $label));
    }

    /**
     * Write the log entry for a model, unless it is not a MetaModel item or logging is off for it.
     *
     * @param ModelInterface $model   The model.
     * @param callable       $message Builds the log message from the model's rendered label.
     *
     * @return void
     */
    private function log(ModelInterface $model, callable $message): void
    {
        $tableName = $model->getProviderName();
        if (!\in_array($tableName, $this->factory->collectNames(), true)) {
            return;
        }

        $metaModel = $this->factory->getMetaModel($tableName);
        if (null === $metaModel || !(bool) $metaModel->get('enableLogging')) {
            return;
        }

        $this->logger->info($message($this->describe($model)));
    }

    /**
     * Name a model the same way its edit mask headline and breadcrumb do.
     *
     * @param ModelInterface $model The model.
     *
     * @return string
     */
    private function describe(ModelInterface $model): string
    {
        $tableName = $model->getProviderName();
        $metaModel = $this->factory->getMetaModel($tableName);
        $modelName = null !== $metaModel ? $metaModel->getName() : $tableName;

        /** @var array<string, mixed>|null $screen */
        $screen  = $this->viewCombination->getScreen($tableName);
        $pattern = (string) ($screen['meta']['subheadline'] ?? '');
        $label   = $this->labelRenderer->render($pattern, $model->getPropertiesAsArray());

        return $modelName . ': ' . ('' !== $label ? $label : (string) $model->getId());
    }
}
