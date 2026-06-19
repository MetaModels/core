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

namespace MetaModels\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent;
use MetaModels\Attribute\ITranslated;
use MetaModels\IFactory;
use MetaModels\ITranslatedMetaModel;

/**
 * Copies translated attribute data for all languages when an item is duplicated.
 *
 * The normal DC_General duplicate path only saves data for the currently active language. This listener iterates every
 * language and copies each translated attribute's raw (non-fallback) data from the source item to the new item.
 *
 * Two paths lead here:
 *  - The simple copy action (Contao2BackendView CopyHandler) persists the clone first and then dispatches the
 *    post-duplicate event, so the new item already has an id and the copy runs right away.
 *  - The clipboard / manual sorting paste path (DefaultController::doCloneAction) dispatches the post-duplicate event
 *    *before* the clone is persisted, so the new item has no id yet. In that case we remember the source id and run
 *    the copy on the subsequent post-paste event, when the clone has been saved and carries a real id.
 */
final class CopyTranslatedData
{
    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * Source item ids for clones whose copy was deferred because the new item had no id yet.
     *
     * Keyed by the spl_object_id() of the (not yet persisted) new model. The clipboard paste path reuses the very same
     * model instance for the later post-paste event, so the object id is a stable correlation key within the request.
     *
     * @var array<int, string>
     */
    private array $deferredSourceIds = [];

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
     * Copy translated data for all languages after a model has been duplicated.
     *
     * @param PostDuplicateModelEvent $event The event.
     *
     * @return void
     */
    public function handle(PostDuplicateModelEvent $event): void
    {
        $newModel = $event->getModel();
        $sourceId = (string) $event->getSourceModel()->getId();
        $newId    = (string) $newModel->getId();

        if ('' === $sourceId) {
            return;
        }

        // Clipboard / manual sorting path: the clone has not been persisted yet and therefore has no id. Defer the
        // copy until the post-paste event fires with a real id.
        if ('' === $newId) {
            $this->deferredSourceIds[\spl_object_id($newModel)] = $sourceId;

            return;
        }

        if ($sourceId === $newId) {
            return;
        }

        $this->copyAllLanguages($newModel->getProviderName(), $sourceId, $newId);
    }

    /**
     * Run a deferred copy after the clone has been persisted via the clipboard paste path.
     *
     * @param PostPasteModelEvent $event The event.
     *
     * @return void
     */
    public function handlePostPaste(PostPasteModelEvent $event): void
    {
        $model    = $event->getModel();
        $objectId = \spl_object_id($model);

        if (!isset($this->deferredSourceIds[$objectId])) {
            return;
        }

        $sourceId = $this->deferredSourceIds[$objectId];
        unset($this->deferredSourceIds[$objectId]);

        $newId = (string) $model->getId();
        if ('' === $newId || $sourceId === $newId) {
            return;
        }

        $this->copyAllLanguages($model->getProviderName(), $sourceId, $newId);
    }

    /**
     * Copy all translated attributes for every language of the MetaModel.
     *
     * @param string $providerName The MetaModel name.
     * @param string $sourceId     The source item ID.
     * @param string $newId        The new item ID.
     *
     * @return void
     */
    private function copyAllLanguages(string $providerName, string $sourceId, string $newId): void
    {
        $metaModel = $this->factory->getMetaModel($providerName);
        if (!$metaModel instanceof ITranslatedMetaModel) {
            return;
        }

        foreach ($metaModel->getLanguages() as $language) {
            $this->copyLanguage($metaModel, $language, $sourceId, $newId);
        }
    }

    /**
     * Copy all translated attributes for a single language.
     *
     * @param ITranslatedMetaModel $metaModel The MetaModel.
     * @param string               $language  The language code.
     * @param string               $sourceId  The source item ID.
     * @param string               $newId     The new item ID.
     *
     * @return void
     */
    private function copyLanguage(
        ITranslatedMetaModel $metaModel,
        string $language,
        string $sourceId,
        string $newId
    ): void {
        foreach ($metaModel->getAttributes() as $attribute) {
            if (!$attribute instanceof ITranslated) {
                continue;
            }

            $data = $attribute->getTranslatedDataFor([$sourceId], $language);
            if ([] === $data || !\array_key_exists($sourceId, $data)) {
                continue;
            }

            $attribute->setTranslatedDataFor([$newId => $data[$sourceId]], $language);
        }
    }
}
