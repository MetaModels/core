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

namespace MetaModels\Test\MetaModel;

use Doctrine\DBAL\Connection;
use MetaModels\Attribute\BaseComplex;
use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\TranslatedReference;
use MetaModels\IItem;
use MetaModels\IItems;
use MetaModels\TranslatedMetaModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Saving a non-main language must only drop the values of translated attributes that equal the fallback.
 */
#[CoversClass(TranslatedMetaModel::class)]
class TranslatedMetaModelUpdateVariantsTest extends TestCase
{
    /**
     * An untranslated attribute has one value for all languages - clearing it would delete the main language value.
     */
    public function testKeepsUntranslatedAttributeWithFallbackValue(): void
    {
        $attribute = $this->createStub(BaseComplex::class);

        $calls = $this->updateInLanguage($attribute, ['2', '3'], ['2', '3']);

        self::assertSame([], $calls['cleared']);
        self::assertSame(['tags'], $calls['saved']);
    }

    /**
     * A translated attribute that equals the fallback is cleared, the fallback then shines through.
     */
    public function testClearsTranslatedAttributeWithFallbackValue(): void
    {
        $attribute = $this->createStub(TranslatedReference::class);

        $calls = $this->updateInLanguage($attribute, ['2', '3'], ['2', '3']);

        self::assertSame(['tags'], $calls['cleared']);
        self::assertSame([], $calls['saved']);
    }

    /**
     * A translated attribute differing from the fallback is saved.
     */
    public function testSavesTranslatedAttributeWithOwnValue(): void
    {
        $attribute = $this->createStub(TranslatedReference::class);

        $calls = $this->updateInLanguage($attribute, ['3', '2'], ['2', '3']);

        self::assertSame([], $calls['cleared']);
        self::assertSame(['tags'], $calls['saved']);
    }

    /**
     * Run updateVariants() for the language "nl" (main language "de").
     *
     * @param IAttribute   $attribute     The attribute "tags" to save.
     * @param list<string> $value         The value of the item to save.
     * @param list<string> $fallbackValue The value of the item in the main language.
     *
     * @return array{cleared: list<string>, saved: list<string>}
     */
    private function updateInLanguage(IAttribute $attribute, array $value, array $fallbackValue): array
    {
        $attribute->method('getColName')->willReturn('tags');
        $attribute->method('valueToWidget')->willReturnArgument(0);

        $metaModel = $this->createMetaModel($this->createItem($fallbackValue));
        $metaModel->addAttribute($attribute);
        $metaModel->selectLanguage('nl');

        $method = new \ReflectionMethod($metaModel, 'updateVariants');
        $method->invoke($metaModel, $this->createItem($value), 'nl', [], false);

        return ['cleared' => $metaModel->cleared, 'saved' => $metaModel->saved];
    }

    /**
     * @param list<string> $value The value of the attribute "tags".
     */
    private function createItem(array $value): IItem
    {
        $item = $this->createStub(IItem::class);
        $item->method('get')->willReturnCallback(
            static fn (string $name): mixed => 'id' === $name ? '28' : $value
        );
        $item->method('isAttributeSet')->willReturn(true);
        $item->method('isVariantBase')->willReturn(false);
        $item->method('isVariant')->willReturn(false);
        $item->method('getSetAttributes')->willReturn(['tags']);

        return $item;
    }

    /**
     * A MetaModel without database that records which attributes got cleared and saved.
     */
    private function createMetaModel(IItem $fallbackItem): TranslatedMetaModel
    {
        $items = $this->createStub(IItems::class);
        $items->method('getItem')->willReturn($fallbackItem);

        return new class (
            [
                'languages' => ['de' => ['isfallback' => true], 'nl' => ['isfallback' => false]],
            ],
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(Connection::class),
            $items
        ) extends TranslatedMetaModel {
            /** @var list<string> */
            public array $cleared = [];

            /** @var list<string> */
            public array $saved = [];

            public function __construct(
                array $data,
                EventDispatcherInterface $dispatcher,
                Connection $connection,
                private readonly IItems $items
            ) {
                parent::__construct($data, $dispatcher, $connection);
            }

            protected function getItemsWithId($arrIds, $arrAttrOnly = [])
            {
                return $this->items;
            }

            protected function clearAttribute(IAttribute $attribute, array $idList, string $langCode): void
            {
                $this->cleared[] = $attribute->getColName();
            }

            protected function saveAttribute($objAttribute, $arrIds, $varData, $strLangCode)
            {
                $this->saved[] = $objAttribute->getColName();
            }
        };
    }
}
