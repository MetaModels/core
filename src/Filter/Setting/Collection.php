<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
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
    protected $arrData = [];

    /**
     * The filter settings contained.
     *
     * @var ISimple[]
     */
    protected $arrSettings = [];

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
        return ($this->arrData[$key] ?? null);
    }

    /**
     * Set the MetaModel.
     *
     * @param IMetaModel $metaModel The MetaModel instance.
     *
     * @return Collection
     */
    public function setMetaModel($metaModel): self
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
    public function addSetting($setting): self
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
            if (in_array($objSetting->get('id'), $arrIgnoredFilter, false)) {
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
        $filterUrl = [];
        foreach ($this->arrSettings as $objSetting) {
            $filterUrl[] = $objSetting->generateFilterUrlFrom($objItem, $objRenderSetting);
        }

        return empty($parameters) ? [] : array_merge(...$parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        $parameters = [];
        foreach ($this->arrSettings as $objSetting) {
            $parameters[] = $objSetting->getParameters();
        }

        return empty($parameters) ? [] : array_merge(...$parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterDCA()
    {
        $parameters = [];
        foreach ($this->arrSettings as $objSetting) {
            $parameters[] = $objSetting->getParameterDCA();
        }

        return empty($parameters) ? [] : array_merge(...$parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterNames()
    {
        $parameters = [];

        foreach ($this->arrSettings as $objSetting) {
            $parameters[] = $objSetting->getParameterFilterNames();
        }

        return empty($parameters) ? [] : array_merge(...$parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterWidgets(
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ) {
        $parameters = [];

        // Get the id with all enabled filter.
        $objFilter = $this->getMetaModel()->getEmptyFilter();
        $this->addRules($objFilter, $arrFilterUrl);

        $arrBaseIds = $objFilter->getMatchingIds();

        foreach ($this->arrSettings as $setting) {
            if ($setting->get('skipfilteroptions')) {
                $objFilter = $this->getMetaModel()->getEmptyFilter();
                $this->addRules($objFilter, $arrFilterUrl, array($setting->get('id')));
                $ids = $objFilter->getMatchingIds();
            } else {
                $ids = $arrBaseIds;
            }

            $parameters[] =
                $setting->getParameterFilterWidgets($ids, $arrFilterUrl, $arrJumpTo, $objFrontendFilterOptions);
        }

        return empty($parameters) ? [] : array_merge(...$parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferencedAttributes()
    {
        $attributes = [];

        foreach ($this->arrSettings as $setting) {
            $attributes[] = $setting->getReferencedAttributes();
        }

        return empty($parameters) ? [] : array_merge(...$parameters);
    }
}
