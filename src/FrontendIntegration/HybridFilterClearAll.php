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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

use Contao\System;

/**
 * Content element clearing the FE-filter.
 *
 * @property \FrontendTemplate $Template
 */
abstract class HybridFilterClearAll extends MetaModelHybrid
{
    /**
     * The name to display in the wildcard.
     *
     * @var string
     */
    protected $wildCardName = '### METAMODEL FILTER ELEMENT CLEAR ALL ###';

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mm_filter_clearall';

    /**
     * The current element type.
     *
     * @var string
     */
    protected $type;

    /**
     * Generate the list.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            return parent::generate();
        }

        return sprintf('[[[metamodelfrontendfilterclearall::%s::%s]]]', $this->type, $this->id);
    }

    /**
     * Generate the module.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function compile()
    {
        $blnActiveParam   = false;
        $filterUrlBuilder = System::getContainer()->get('metamodels.filter_url');
        $filterUrl        = $filterUrlBuilder->getCurrentFilterUrl();
        foreach ($GLOBALS['MM_FILTER_PARAMS'] as $param) {
            if ($filterUrl->hasSlug($param)) {
                $filterUrl->setSlug($param, '');
                $blnActiveParam = true;
                continue;
            }
            if ($filterUrl->hasGet($param)) {
                $filterUrl->setGet($param, '');
                $blnActiveParam = true;
                continue;
            }
        }

        // Check if we have filter and if we have active params.
        $this->Template->active      = (
            !is_array($GLOBALS['MM_FILTER_PARAMS'])
            || count($GLOBALS['MM_FILTER_PARAMS']) == 0
        );
        $this->Template->activeParam = $blnActiveParam;

        // Build FE url.
        $this->Template->href = $filterUrlBuilder->generate($filterUrl);
    }

    /**
     * Call the generate() method from parent class.
     *
     * @return string
     */
    public function generateReal()
    {
        // Fallback template.
        if (!empty($this->metamodel_fef_template)) {
            $this->strTemplate = $this->metamodel_fef_template;
        }

        return parent::generate();
    }
}
