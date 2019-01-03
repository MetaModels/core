<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use MetaModels\Filter\IFilter;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\Render\Setting\ICollection as IRenderSettings;

/**
 * This interface handles all filter setting abstraction.
 */
interface ICollection
{
    /**
     * Get the named property from the filter setting.
     *
     * @param string $key The name of the property to retrieve.
     *
     * @return mixed|null
     */
    public function get($key);

    /**
     * Retrieve the MetaModel this filter belongs to.
     *
     * @return IMetaModel
     *
     * @throws \RuntimeException When the MetaModel can not be determined.
     */
    public function getMetaModel();

    /**
     * Generates all filter rules from the contained filter settings.
     *
     * @param IFilter $objFilter        The filter object to add rules to.
     *
     * @param array   $arrFilterUrl     The filter url to be applied.
     *
     * @param array   $arrIgnoredFilter An optional list with filter ids that should be ignored.
     *                                  Defaults to empty array.
     *
     * @return void
     */
    public function addRules(IFilter $objFilter, $arrFilterUrl, $arrIgnoredFilter = array());

    /**
     * Generate an filter url (aka jump to url) according to the contained filter rules.
     *
     * @param IItem           $objItem          The item from which the values shall be retrieved from.
     *
     * @param IRenderSettings $objRenderSetting The render settings that hold the destination filter settings and
     *                                          jumpTo page.
     *
     * @return array The filter url parameters.
     */
    public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting);

    /**
     * Retrieve a list of all registered parameters from the setting.
     *
     * @return array
     */
    public function getParameters();

    /**
     * Retrieve the names of all parameters for listing in frontend filter configuration.
     *
     * @return string[] the parameters as array. parametername => label
     */
    public function getParameterFilterNames();

    /**
     * Retrieve a list of filter widgets for all registered parameters as form field arrays.
     *
     * @param array                 $arrFilterUrl             The current filter url.
     *
     * @param array                 $arrJumpTo                The selected jump to page to use for link generating.
     *
     * @param FrontendFilterOptions $objFrontendFilterOptions The frontend filter options to be passed to the widget.
     *
     * @return array
     */
    public function getParameterFilterWidgets(
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    );

    /**
     * Retrieve a list of all registered parameters from the setting as DCA compatible arrays.
     *
     * @return array
     */
    public function getParameterDCA();

    /**
     * Retrieve a list of all referenced attributes within the filter setting.
     *
     * @return array
     */
    public function getReferencedAttributes();
}
