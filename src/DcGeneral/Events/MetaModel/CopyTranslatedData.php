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
 *    *before* the clone is persisted, so the new item has no id yet. In that case we stash the source id as meta
 *    information on the clone model and run the copy on the subsequent post-paste event, when the clone has been saved
 *    and carries a real id. The clipboard paste path reuses the very same model instance for the later post-paste
 *    event, so the stashed meta value travels with it. Keeping the state on the model (instead of in the listener)
 *    means this service stays stateless and the deferred id cannot leak across paste operations.
 */
final class CopyTranslatedData
{
    /**
     * Meta key under which the deferred source id is stashed on the not-yet-persisted clone model.
     */
    private const META_DEFERRED_SOURCE_ID = 'metamodels_copy_translated_source_id';

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

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
            $newModel->setMeta(self::META_DEFERRED_SOURCE_ID, $sourceId);

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
        $sourceId = (string) $model->getMeta(self::META_DEFERRED_SOURCE_ID);

        if ('' === $sourceId) {
            return;
        }

        $model->setMeta(self::META_DEFERRED_SOURCE_ID, null);

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
