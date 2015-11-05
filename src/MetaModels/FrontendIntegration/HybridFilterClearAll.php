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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

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
    protected $wildCardName = '### METAMODEL FILTER CLEAR ALL ###';

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
        $arrPage          = $GLOBALS['objPage']->row();
        $arrGetParameters = array();

        foreach (array_keys($_GET) as $mixGetKey) {
            if (in_array($mixGetKey, $GLOBALS['MM_FILTER_PARAMS'])) {
                $blnActiveParam = true;
                continue;
            }

            $arrGetParameters[$mixGetKey] = \Input::get($mixGetKey);
        }

        // Check if we have filter and if we have active params.
        $this->Template->active      = (
            !is_array($GLOBALS['MM_FILTER_PARAMS'])
            || count($GLOBALS['MM_FILTER_PARAMS']) == 0
        );
        $this->Template->activeParam = $blnActiveParam;

        // Build FE url.
        $this->Template->href = $this->generateFrontendUrl($arrPage, $this->getJumpToUrl($arrGetParameters));
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

    /**
     * Generate an url determined by the given params and configured jumpTo page.
     *
     * @param array $arrParams The URL parameters to use.
     *
     * @return string the generated URL.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getJumpToUrl($arrParams)
    {
        $strFilterAction = '';
        foreach ($arrParams as $strName => $varParam) {
            // Skip the magic "language" parameter.
            if (($strName == 'language') && $GLOBALS['TL_CONFIG']['addLanguageToUrl']) {
                continue;
            }

            $strValue = $varParam;

            if (is_array($varParam)) {
                $strValue = implode(',', array_filter($varParam));
            }

            if (strlen($strValue)) {
                // Shift auto_item to the front.
                if ($strName == 'auto_item') {
                    $strFilterAction = '/' . $strValue . $strFilterAction;
                    continue;
                }

                $strFilterAction .= sprintf(
                    $GLOBALS['TL_CONFIG']['disableAlias'] ? '&amp;%s=%s' : '/%s/%s',
                    $strName,
                    urlencode($strValue)
                );
            }
        }
        return $strFilterAction;
    }
}
