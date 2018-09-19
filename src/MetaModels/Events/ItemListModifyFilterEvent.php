<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Events;

use MetaModels\ItemList;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is fired within the modifyFilter() of an ItemList.
 */
class ItemListModifyFilterEvent extends Event
{

    /**
     * The item list.
     *
     * @var ItemList
     */
    private $itemList;

    /**
     * ItemListModifyFilterEvent constructor.
     *
     * @param ItemList $itemList The item list.
     */
    public function __construct(ItemList $itemList)
    {
        $this->itemList = $itemList;
    }

    /**
     * Returns the item list.
     *
     * @return ItemList
     */
    public function getItemList()
    {
        return $this->itemList;
    }
}
