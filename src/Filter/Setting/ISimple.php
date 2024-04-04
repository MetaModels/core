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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use MetaModels\Filter\IFilter;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\Render\Setting\ICollection as IRenderSettings;

/**
 * This interface handles the abstraction for a single filter setting.
 */
interface ISimple
{
    /**
     * Return the value of the requested attribute.
     *
     * @param string $strKey Name of the attribute to retrieve.
     *
     * @return mixed The stored value, if any.
     */
    public function get($strKey);

    /**
     * Tells the filter setting to add all of its rules to the passed filter object.
     *
     * The filter rules can evaluate the also passed filter url.
     *
     * A filter url hereby is a simple hash of name => value layout, it may eventually be interpreted
     * by attributes via IMetaModelAttribute::searchFor() method.
     *
     * @param IFilter              $objFilter    The filter to append the rules to.
     * @param array<string, mixed> $arrFilterUrl The parameters to evaluate.
     *
     * @return void
     */
    public function prepareRules(IFilter $objFilter, $arrFilterUrl);

    /**
     * Generate all URL parameters understood/required by this filter setting.
     *
     * This method is being called when a frontend "jumpTo" URL is being generated and the
     * parameters have to be fetched.
     *
     * @param IItem           $objItem          The item to fetch the values from.
     * @param IRenderSettings $objRenderSetting The render setting to be applied.
     *
     * @return array<string, string> An array containing all the URL parameters needed by this filter setting.
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting);

    /**
     * Retrieve a list of all registered parameters from the setting.
     *
     * @return list<string>
     */
    public function getParameters();

    /**
     * Retrieve a list of all registered parameters from the setting as DCA compatible arrays.
     *
     * These parameters may be overridden by modules and content elements and the like.
     *
     * @return array<string, mixed>
     */
    public function getParameterDCA();

    /**
     * Retrieve the names of all parameters for listing in frontend filter configuration.
     *
     * @return array<string, string> the parameters as array. parametername => label
     */
    public function getParameterFilterNames();

    /**
     * Retrieve a list of filter widgets for all registered parameters as form field arrays.
     *
     * @param list<string>|null     $arrIds                   The ids matching the current filter values.
     * @param array<string, mixed>  $arrFilterUrl             The current filter url.
     * @param array<string, mixed>  $arrJumpTo                The jumpTo page (array, row data from tl_page).
     * @param FrontendFilterOptions $objFrontendFilterOptions The frontend filter options.
     *
     * @return array<string, mixed>
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function getParameterFilterWidgets(
        $arrIds,
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    );

    /**
     * Retrieve a list of all referenced attributes within the filter setting.
     *
     * @return list<string>
     */
    public function getReferencedAttributes();
}
