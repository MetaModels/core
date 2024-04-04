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

namespace MetaModels\Attribute;

/**
 * Interface for "complex" MetaModel attributes.
 * Complex attributes are attributes that can not be fetched with a simple:
 * "SELECT colName FROM mm_table" and therefore need to be handled differently.
 */
interface IComplex extends IAttribute
{
    /**
     * This method is called to retrieve the data for certain items from the database.
     *
     * @param list<string> $arrIds The ids of the items to retrieve.
     *
     * @return array<string, mixed> The nature of the resulting array is a mapping from id => "native data" where
     *                              the definition of "native data" is only of relevance to the given item.
     */
    public function getDataFor($arrIds);

    /**
     * Remove values for items.
     *
     * @param list<string> $arrIds The ids of the items to retrieve.
     *
     * @return void
     */
    public function unsetDataFor($arrIds);
}
