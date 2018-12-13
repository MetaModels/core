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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

/**
 * This class serves as an container for various information how to display filter widgets in the frontend.
 */
class FrontendFilterOptions
{
    /**
     * Auto submit.
     *
     * @var bool
     */
    protected $blnAutoSubmit = true;

    /**
     * Hide clear filter.
     *
     * @var bool
     */
    protected $blnHideClearFilter = false;

    /**
     * Show the count values.
     *
     * @var bool
     */
    protected $blnShowCountValues = false;

    /**
     * Check if the filter shall be automatically submitted.
     *
     * @return bool
     */
    public function isAutoSubmit()
    {
        return $this->blnAutoSubmit;
    }

    /**
     * Set if the filter shall be automatically submitted.
     *
     * @param bool $blnAutoSubmit True to auto submit false otherwise.
     *
     * @return void
     */
    public function setAutoSubmit($blnAutoSubmit)
    {
        $this->blnAutoSubmit = $blnAutoSubmit;
    }

    /**
     * Check if the "clear filter" option shall be hidden or visible.
     *
     * @return bool
     */
    public function isHideClearFilter()
    {
        return $this->blnHideClearFilter;
    }

    /**
     * Set if the "clear filter" option shall be hidden or visible.
     *
     * @param bool $blnHideClearFilter True to hide the "clear filter" option, false otherwise.
     *
     * @return void
     */
    public function setHideClearFilter($blnHideClearFilter)
    {
        $this->blnHideClearFilter = $blnHideClearFilter;
    }

    /**
     * Check if the amount of matches shall be shown for each filter option.
     *
     * @return bool
     */
    public function isShowCountValues()
    {
        return $this->blnShowCountValues;
    }

    /**
     * Set if the amount of matches shall be shown for each filter option.
     *
     * @param bool $blnShowCountValues True to show the amount for each option, false otherwise.
     *
     * @return void
     */
    public function setShowCountValues($blnShowCountValues)
    {
        $this->blnShowCountValues = $blnShowCountValues;
    }
}
