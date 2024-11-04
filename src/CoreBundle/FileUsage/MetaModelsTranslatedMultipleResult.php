<?php

declare(strict_types=1);

namespace MetaModels\CoreBundle\FileUsage;

use InspiredMinds\ContaoFileUsage\Result\ResultInterface;

final class MetaModelsTranslatedMultipleResult implements ResultInterface
{
    public function __construct(
        private string $tableName,
        private string $attributeName,
        private string $itemId,
        private string $language,
        private string $editUrl,
    ) {
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    public function getItemId(): string
    {
        return $this->itemId;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getEditUrl(): string
    {
        return $this->editUrl;
    }

    public function getTemplate(): string
    {
        return '@MetaModelsCore/FileUsage/file_usage_translated_multiple_result.html.twig';
    }
}
