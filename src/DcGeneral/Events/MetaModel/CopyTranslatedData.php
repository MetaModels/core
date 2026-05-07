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
use MetaModels\Attribute\ITranslatedWithFallbackControl;
use MetaModels\IFactory;
use MetaModels\ITranslatedMetaModel;

/**
 * Copies translated attribute data for all languages when an item is duplicated.
 *
 * The normal DC_General duplicate path only saves data for the currently active language. This listener runs after
 * the copy has been persisted and iterates every language, copying each translated attribute's raw (non-fallback) data
 * from the source item to the new item.
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
        $sourceId = (string) $event->getSourceModel()->getId();
        $newId    = (string) $event->getModel()->getId();

        if ('' === $sourceId || '' === $newId || $sourceId === $newId) {
            return;
        }

        $metaModel = $this->factory->getMetaModel($event->getModel()->getProviderName());
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
            if (!$attribute instanceof ITranslatedWithFallbackControl) {
                continue;
            }

            $data = $attribute->getTranslatedDataForWithoutFallback([$sourceId], $language);
            if ([] === $data || !isset($data[$sourceId])) {
                continue;
            }

            $attribute->applyTranslatedDataFor([$newId => $data[$sourceId]], $language);
        }
    }
}
