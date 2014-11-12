<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

/**
 * Content element clearing the FE-filter.
 *
 * @package    MetaModels
 * @subpackage FrontendClearAll
 * @author     Stefan Heimes <cms@men-at-work.de>
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
     * Generate the list.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            return parent::generate();
        }

        // Fallback template.
        if (!empty($this->metamodel_fef_template)) {
            $this->strTemplate = $this->metamodel_fef_template;
        }

        return sprintf('[[[metamodelfrontendfilterclearall::mod::%s]]]', $this->id);
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

        // @codingStandardsIgnoreStart - Skip filter params, loop over $_GET to get a list of all keys.
        foreach (array_keys($_GET) as $mixGetKey)
            // @codingStandardsIgnoreEnd - Continue with style checking.
        {
            if (in_array($mixGetKey, $GLOBALS['MM_FILTER_PARAMS'])) {
                $blnActiveParam = true;
                continue;
            }

            $arrGetParameters[$mixGetKey] = \Input::getInstance()->get($mixGetKey);
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

            $strValue = str_replace(array('/', '\''), array('-slash-', '-apos-'), $strValue);

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
