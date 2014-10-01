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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Render;

use MetaModels\Helper\ContaoController;

/**
 * Template class for metamodels.
 * In most aspects this behaves identically to the FrontendTemplate class from Contao but it differs in respect to
 * format selection.
 * The format is being determined upon parsing and not upon instantiation. There is also an optional "fail on not
 * found" flag,which defaults to false and therefore one can parse the template and have zero output instead of
 * cluttering the frontend with exceptions.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Template
{
    /**
     * Template file.
     *
     * @var string
     */
    protected $strTemplate;

    /**
     * Output buffer.
     *
     * @var string
     */
    protected $strBuffer;

    /**
     * Template data.
     *
     * @var array
     */
    protected $arrData = array();

    /**
     * Current output format. Only valid when within {@link MetaModelTemplate::parse()}.
     *
     * @var string
     */
    protected $strFormat = null;

    /**
     * Makes all protected methods from class Controller callable publically.
     *
     * @param string $strMethod The method name.
     *
     * @param array  $arrArgs   The parameters for the method.
     *
     * @return mixed
     */
    public function __call($strMethod, $arrArgs)
    {
        return call_user_func_array(array(ContaoController::getInstance(), $strMethod), $arrArgs);
    }

    /**
     * Create a new template instance.
     *
     * @param string $strTemplate The name of the template file.
     */
    public function __construct($strTemplate = '')
    {
        $this->strTemplate = $strTemplate;
    }

    /**
     * Set an object property.
     *
     * @param string $strKey   The name of the property.
     *
     * @param mixed  $varValue The value to set.
     *
     * @return void
     */
    public function __set($strKey, $varValue)
    {
        $this->arrData[$strKey] = $varValue;
    }

    /**
     * Return an object property.
     *
     * @param string $strKey The name of the property.
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function __get($strKey)
    {
        if ($GLOBALS['TL_CONFIG']['debugMode'] && !array_key_exists($strKey, $this->arrData)) {
            trigger_error($this->getName() . ': Undefined template variable: ' . $strKey, E_USER_WARNING);

            return null;
        }
        return $this->arrData[$strKey];
    }

    /**
     * Check whether a property is set.
     *
     * @param string $strKey The name of the property.
     *
     * @return boolean
     */
    public function __isset($strKey)
    {
        return isset($this->arrData[$strKey]);
    }

    /**
     * Set the template data from an array.
     *
     * @param array $arrData The properties to be set.
     *
     * @return void
     */
    public function setData($arrData)
    {
        $this->arrData = $arrData;
    }

    /**
     * Return the template data as array.
     *
     * @return array
     */
    public function getData()
    {
        return $this->arrData;
    }

    /**
     * Set the template name.
     *
     * @param string $strTemplate The new name.
     *
     * @return void
     */
    public function setName($strTemplate)
    {
        $this->strTemplate = $strTemplate;
    }

    /**
     * Return the template name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->strTemplate;
    }

    /**
     * Print all template variables to the screen using print_r.
     *
     * @return void
     */
    public function showTemplateVars()
    {
        echo "<pre>\n";
        // @codingStandardsIgnoreStart - We really want to keep this debug function here.
        print_r($this->arrData);
        // @codingStandardsIgnoreEnd
        echo "</pre>\n";
    }

    /**
     * Print all template variables to the screen using var_dump.
     *
     * @return void
     */
    public function dumpTemplateVars()
    {
        echo "<pre>\n";
        // @codingStandardsIgnoreStart - We really want to keep this debug function here.
        var_dump($this->arrData);
        // @codingStandardsIgnoreEnd
        echo "</pre>\n";
    }

    /**
     * Find a particular template file and return its path.
     *
     * @param string $strTemplate       Name of the template file.
     *
     * @param string $strFormat         The format to search for.
     *
     * @param bool   $blnFailIfNotFound Boolean flag telling if an Exception shall be thrown when the file can not
     *                                  be found.
     *
     * @throws \Exception When the flag has been set and the file has not been found.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getTemplate211($strTemplate, $strFormat = 'html5', $blnFailIfNotFound = false)
    {
        $strKey = $strFilename = $strTemplate . '.' . $strFormat;

        $strTemplateGroup = '';
        // Check for a theme folder.
        if (TL_MODE == 'FE') {
            $strTemplateGroup = str_replace(array('../', 'templates/'), '', $GLOBALS['objPage']->templateGroup);

            if ($strTemplateGroup != '') {
                $strKey = $strTemplateGroup . '/' . $strKey;
            }
        }

        $objCache = \FileCache::getInstance('templates');

        // Try to load the template path from the cache.
        if (!$GLOBALS['TL_CONFIG']['debugMode'] && isset($objCache->$strKey)) {
            if (file_exists(TL_ROOT . '/' . $objCache->$strKey)) {
                return TL_ROOT . '/' . $objCache->$strKey;
            } else {
                unset($objCache->$strKey);
            }
        }

        $strPath = TL_ROOT . '/templates';

        // Check the theme folder first.
        if (TL_MODE == 'FE' && $strTemplateGroup != '') {
            $strFile = $strPath . '/' . $strTemplateGroup . '/' . $strFilename;

            if (file_exists($strFile)) {
                $objCache->$strKey = 'templates/' . $strTemplateGroup . '/' . $strFilename;
                return $strFile;
            }
        }

        // Then check the global templates directory.
        $strFile = $strPath . '/' . $strFilename;

        if (file_exists($strFile)) {
            $objCache->$strKey = 'templates/' . $strFilename;
            return $strFile;
        }

        // At last browse all module folders in reverse order.
        foreach (array_reverse(\Config::getInstance()->getActiveModules()) as $strModule) {
            $strFile = TL_ROOT . '/system/modules/' . $strModule . '/templates/' . $strFilename;

            if (file_exists($strFile)) {
                $objCache->$strKey = 'system/modules/' . $strModule . '/templates/' . $strFilename;
                return $strFile;
            }
        }

        if ($blnFailIfNotFound) {
            throw new \Exception('Could not find template file "' . $strFilename . '"');
        }

        return null;
    }

    /**
     * Find a particular template file and return its path.
     *
     * @param string $strTemplate       Name of the template file.
     *
     * @param string $strFormat         The format to search for.
     *
     * @param bool   $blnFailIfNotFound Boolean flag telling if an Exception shall be thrown when the file can not
     *                                  be found.
     *
     * @throws \Exception When the flag has been set and the file has not been found.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getTemplate($strTemplate, $strFormat = 'html5', $blnFailIfNotFound = false)
    {
        $strTemplate = basename($strTemplate);

        // Contao 2.X only.
        if (version_compare(VERSION, '3.0', '<')) {
            return $this->getTemplate211($strTemplate, $strFormat, $blnFailIfNotFound);
        }

        // Check for a theme folder.
        if (TL_MODE == 'FE') {
            $strCustom = str_replace('../', '', $GLOBALS['objPage']->templateGroup);

            if ($strCustom != '') {
                return \TemplateLoader::getPath($strTemplate, $strFormat, $strCustom);
            }
        }

        return \TemplateLoader::getPath($strTemplate, $strFormat);
    }

    /**
     * Call the parse Template HOOK.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function callParseTemplateHook()
    {
        if (isset($GLOBALS['METAMODEL_HOOKS']['parseTemplate'])
            && is_array($GLOBALS['METAMODEL_HOOKS']['parseTemplate'])
        ) {
            foreach ($GLOBALS['METAMODEL_HOOKS']['parseTemplate'] as $callback) {
                list($strClass, $strMethod) = $callback;

                $objCallback = (in_array('getInstance', get_class_methods($strClass)))
                    ? call_user_func(array($strClass, 'getInstance'))
                    : new $strClass();

                $objCallback->$strMethod($this);
            }
        }
    }

    /**
     * Parse the template file and return it as string.
     *
     * @param string  $strOutputFormat   The desired output format.
     *
     * @param boolean $blnFailIfNotFound If set to true, the template object will throw an exception if the template
     *                                   can not be found. Defaults to false.
     *
     * @return string The parsed template.
     */
    public function parse($strOutputFormat, $blnFailIfNotFound = false)
    {
        if ($this->strTemplate == '') {
            return '';
        }

        // HOOK: add custom parse filters.
        $this->callParseTemplateHook();

        $strTplFile = $this->getTemplate($this->strTemplate, $strOutputFormat, $blnFailIfNotFound);
        if ($strTplFile) {
            $this->strFormat = $strOutputFormat;

            ob_start();
            include($strTplFile);
            $strBuffer = ob_get_contents();
            ob_end_clean();

            $this->strFormat = null;

            return $strBuffer;
        }
        return '';
    }

    /**
     * Protected as only the included template file shall be able to call.
     *
     * This is needed to remain protected, as outside from {@link Template::parse()} the format is undefined.
     *
     * @return string
     */
    protected function getFormat()
    {
        return $this->strFormat;
    }

    /**
     * Static convenience method to perform the whole rendering within one line of code.
     *
     * @param string $strTemplate       Name of the template file.
     *
     * @param string $strOutputFormat   The desired output format.
     *
     * @param array  $arrTplData        The data to use in the template.
     *
     * @param bool   $blnFailIfNotFound If set to true, the template object will throw an exception if the template
     *                                  can not be found. Defaults to false.
     *
     * @return string
     */
    public static function render($strTemplate, $strOutputFormat, $arrTplData, $blnFailIfNotFound = false)
    {
        $objTemplate = new self($strTemplate);
        $objTemplate->setData($arrTplData);
        return $objTemplate->parse($strOutputFormat, $blnFailIfNotFound);
    }
}
