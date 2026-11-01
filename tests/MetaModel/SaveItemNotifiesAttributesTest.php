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
use MetaModels\Attribute\IAttribute;
use MetaModels\IItem;
use MetaModels\Item;
use MetaModels\MetaModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Attributes that build their value from others (alias, combined values) are told about a saved item, whether or not
 * the value was given.
 */
#[CoversClass(MetaModel::class)]
class SaveItemNotifiesAttributesTest extends TestCase
{
    public function testANewItemTellsEveryAttributeThatItHasBeenSaved(): void
    {
        $alias = $this->attribute('alias');
        $title = $this->attribute('title');
        $alias->expects(self::once())->method('modelSaved');
        $title->expects(self::once())->method('modelSaved');

        $metaModel = $this->createMetaModel($alias, $title);
        $item      = new Item($metaModel, [], new EventDispatcher());
        // Only the title is given, as a script that creates an item does.
        $item->set('title', 'Hello');

        $metaModel->saveItem($item, 1);
    }

    public function testAnExistingItemOnlyTellsTheAttributesItHolds(): void
    {
        $alias = $this->attribute('alias');
        $title = $this->attribute('title');
        $alias->expects(self::never())->method('modelSaved');
        $title->expects(self::once())->method('modelSaved');

        $metaModel = $this->createMetaModel($alias, $title);
        // Loaded with a restricted attribute list: the alias is not part of the item.
        $item = new Item($metaModel, ['id' => '5', 'title' => 'Hello'], new EventDispatcher());

        $metaModel->saveItem($item, 1);
    }

    private function attribute(string $name): IAttribute&MockObject
    {
        $attribute = $this->createMock(IAttribute::class);
        $attribute->method('getColName')->willReturn($name);

        return $attribute;
    }

    private function createMetaModel(IAttribute ...$attributes): MetaModel
    {
        $connection = $this->createStub(Connection::class);

        $dispatcher = $this->createStub(EventDispatcherInterface::class);
        $metaModel  = new class (['translated' => ''], $dispatcher, $connection) extends MetaModel {
            protected function createNewItem($item)
            {
                // The database is not under test.
                $item->set('id', '7');
            }

            protected function saveSimpleColumn($strColumn, $arrIds, $varData)
            {
            }

            protected function updateVariants($item, $activeLanguage, $allIds, $baseAttributes = false)
            {
            }
        };
        foreach ($attributes as $attribute) {
            $metaModel->addAttribute($attribute);
        }

        return $metaModel;
    }
}
