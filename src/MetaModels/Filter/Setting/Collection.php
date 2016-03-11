<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\Filter\IFilter;
use MetaModels\IMetaModel;
use MetaModels\Render\Setting\ICollection as IRenderSettings;

/**
 * This is the ICollection reference implementation.
 */
class Collection implements ICollection
{
    /**
     * The additional meta data for this filter setting collection.
     *
     * @var array
     */
    protected $arrData = array();

    /**
     * The filter settings contained.
     *
     * @var ISimple[]
     */
    protected $arrSettings = array();

    /**
     * The attached MetaModel.
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * Create a new instance.
     *
     * @param array $arrData The meta data for this filter setting collection.
     */
    public function __construct($arrData)
    {
        $this->arrData = $arrData;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return isset($this->arrData[$key]) ? $this->arrData[$key] : null;
    }

    /**
     * Set the MetaModel.
     *
     * @param IMetaModel $metaModel The MetaModel instance.
     *
     * @return Collection
     */
    public function setMetaModel($metaModel)
    {
        $this->metaModel = $metaModel;

        return $this;
    }

    /**
     * Retrieve the MetaModel this filter belongs to.
     *
     * @return IMetaModel
     *
     * @throws \RuntimeException When the MetaModel can not be determined.
     */
    public function getMetaModel()
    {
        if ($this->metaModel) {
            return $this->metaModel;
        }

        throw new \RuntimeException(
            sprintf('Error: Filter setting %d not attached to a MetaModel', $this->arrData['id'])
        );
    }

    /**
     * Add a setting to the collection.
     *
     * @param ISimple|IWithChildren $setting The setting to add.
     *
     * @return Collection
     */
    public function addSetting($setting)
    {
        $this->arrSettings[] = $setting;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addRules(IFilter $objFilter, $arrFilterUrl, $arrIgnoredFilter = array())
    {
        foreach ($this->arrSettings as $objSetting) {
            // If the setting is on the ignore list skip it.
            if (in_array($objSetting->get('id'), $arrIgnoredFilter)) {
                continue;
            }

            $objSetting->prepareRules($objFilter, $arrFilterUrl);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting)
    {
        $arrFilterUrl = array();
        foreach ($this->arrSettings as $objSetting) {
            $arrFilterUrl = array_merge($arrFilterUrl, $objSetting->generateFilterUrlFrom($objItem, $objRenderSetting));
        }
        return $arrFilterUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        $arrParams = array();
        foreach ($this->arrSettings as $objSetting) {
            $arrParams = array_merge($arrParams, $objSetting->getParameters());
        }
        return $arrParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterDCA()
    {
        $arrParams = array();
        foreach ($this->arrSettings as $objSetting) {
            $arrParams = array_merge($arrParams, $objSetting->getParameterDCA());
        }
        return $arrParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterNames()
    {
        $arrParams = array();

        foreach ($this->arrSettings as $objSetting) {
            $arrParams = array_merge($arrParams, $objSetting->getParameterFilterNames());
        }
        return $arrParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterWidgets(
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ) {
        $arrParams = array();

        // Get the id with all enabled filter.
        $objFilter = $this->getMetaModel()->getEmptyFilter();
        $this->addRules($objFilter, $arrFilterUrl);

        $arrBaseIds = $objFilter->getMatchingIds();

        foreach ($this->arrSettings as $objSetting) {
            if ($objSetting->get('skipfilteroptions')) {
                $objFilter = $this->getMetaModel()->getEmptyFilter();
                $this->addRules($objFilter, $arrFilterUrl, array($objSetting->get('id')));
                $arrIds = $objFilter->getMatchingIds();
            } else {
                $arrIds = $arrBaseIds;
            }

            $arrParams = array_merge(
                $arrParams,
                $objSetting->getParameterFilterWidgets($arrIds, $arrFilterUrl, $arrJumpTo, $objFrontendFilterOptions)
            );
        }

        return $arrParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferencedAttributes()
    {
        $arrAttributes = array();

        foreach ($this->arrSettings as $objSetting) {
            $arrAttributes = array_merge($arrAttributes, $objSetting->getReferencedAttributes());
        }

        return $arrAttributes;
    }
}
