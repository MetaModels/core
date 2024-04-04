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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

use MetaModels\Render\Setting\ICollection;

/**
 * Interface for a collection of MetaModel items.
 *
 * @method IItems|bool next() Advance the internal cursor by one returns the current instance or false when last
 *                            item has had been reached.
 * @extends \Iterator<int, IItem>
 * @extends \ArrayAccess<int, IItem>
 */
interface IItems extends \Iterator, \ArrayAccess
{
    /**
     * Return the current item.
     *
     * @return IItem|null
     */
    public function getItem();

    /**
     * Return the amount of contained items.
     *
     * @return int the amount of contained items.
     */
    public function getCount();

    /**
     * Reset to the first element in the collection.
     *
     * @return IItems|bool true if there are items contained, false otherwise.
     */
    public function first();

    /**
     * Go to the previous row of the current result.
     *
     * @return IItems|boolean the current instance or false if no previous item is present.
     */
    public function prev();

    /**
     * Go to the last row of the current result.
     *
     * @return IItems|boolean the current instance or false if no item is present.
     */
    public function last();

    /**
     * Reset the current result.
     *
     * @return IItems the current instance.
     */
    public function reset();

    /**
     * Get the CSS classes for the current item.
     *
     * The class will be combined of:
     * * first - if the item is the first in the collection
     * * last  - if the item is the first in the collection
     * * even  - if the item is on even position
     * * odd   - if the item is on odd position
     *
     * @return string the CSS class
     */
    public function getClass();

    /**
     * Parses the current item in the desired output format using the format settings.
     *
     * @param string           $strOutputFormat Optional, defaults to text. The output format to use.
     * @param ICollection|null $objSettings     Optional, defaults to null. The additional settings.
     *
     * @return array the parsed information.
     */
    public function parseValue($strOutputFormat = 'text', $objSettings = null);

    /**
     * Parses all items in the desired output format using the format settings.
     *
     * @param string           $strOutputFormat Optional, defaults to text. The output format to use.
     * @param ICollection|null $objSettings     Optional, defaults to null. The additional settings.
     *
     * @return array the parsed information.
     */
    public function parseAll($strOutputFormat = 'text', $objSettings = null);
}
