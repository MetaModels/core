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

use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\Render\Setting\ICollection as IRenderSettings;

use function array_merge;

/**
 * Base implementation for settings that can contain children.
 */
abstract class WithChildren extends Simple implements IWithChildren
{
    /**
     * All child settings embedded in this setting.
     *
     * @var ISimple[]
     */
    protected $arrChildren = [];

    /**
     * {@inheritdoc}
     */
    public function addChild(ISimple $objFilterSetting)
    {
        $this->arrChildren[] = $objFilterSetting;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting)
    {
        $arrFilterUrl = array();
        foreach ($this->arrChildren as $objSetting) {
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
        foreach ($this->arrChildren as $objSetting) {
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
        foreach ($this->arrChildren as $objSetting) {
            $arrParams = array_merge($arrParams, $objSetting->getParameterDCA());
        }
        return $arrParams;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function getParameterFilterWidgets(
        $arrIds,
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ) {
        $arrParams = [];
        foreach ($this->arrChildren as $objSetting) {
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
    public function getParameterFilterNames()
    {
        $arrParams = [];
        foreach ($this->arrChildren as $objSetting) {
            $arrParams = array_merge($arrParams, $objSetting->getParameterFilterNames());
        }
        return $arrParams;
    }

    /**
     * Retrieve a list of all referenced attributes within the filter setting.
     *
     * @return list<string>
     */
    public function getReferencedAttributes()
    {
        $arrAttributes = [];
        foreach ($this->arrChildren as $objSetting) {
            $arrAttributes = array_merge($arrAttributes, $objSetting->getReferencedAttributes());
        }
        return $arrAttributes;
    }
}
