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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\BackendIntegration\InputScreen;

use MetaModels\IMetaModel;

/**
 * This interface describes the abstraction of an input screen grouping and sorting information.
 */
interface IInputScreenGroupingAndSorting
{
    /**
     * Get the MetaModel the input screen belongs to.
     *
     * @return IMetaModel
     */
    public function getMetaModel();

    /**
     * Retrieve the grouping type.
     *
     * @return string
     */
    public function getRenderGroupType();

    /**
     * Retrieve the amount of chars to use for grouping.
     *
     * @return string
     */
    public function getRenderGroupLength();

    /**
     * Retrieve the name of the attribute to use for grouping.
     *
     * @return string
     */
    public function getRenderGroupAttribute();

    /**
     * Retrieve the sorting direction.
     *
     * @return string
     */
    public function getRenderSortDirection();

    /**
     * Retrieve the render sort attribute name.
     *
     * @return string
     */
    public function getRenderSortAttribute();

    /**
     * Determine if manual sorting is enabled or not.
     *
     * @return bool
     */
    public function isManualSorting();

    /**
     * Determine if this is the default sorting.
     *
     * @return bool
     */
    public function isDefault();

    /**
     * Retrieve the name.
     *
     * @return string
     */
    public function getName();
}
