<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Template class for metamodels.
 * In most aspects this behaves identically to the FrontendTemplate class from Contao but it differs in respect to format selection.
 * The format is being determined upon parsing and not upon instantiation. There is also an optional "fail on not found" flag,
 * which defaults to false and therefore one can parse the template and have zero output instead of cluttering the frontend with exceptions.
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelTemplate
{
	/**
	 * Template file
	 * @var string
	 */
	protected $strTemplate;

	/**
	 * Output buffer
	 * @var string
	 */
	protected $strBuffer;

	/**
	 * Template data
	 * @var array
	 */
	protected $arrData = array();

	/**
	 * current output format. Only valid when within {@link MetaModelTemplate::parse()}
	 * @var string
	 */
	protected $strFormat = NULL;

	/**
	 * Makes all protected methods from class Controller callable publically.
	 */
	public function __call($strMethod, $arrArgs)
	{
		return call_user_func_array(array(MetaModelController::getInstance(), $strMethod), $arrArgs);
	}

	/**
	 * Create a new template instance
	 * @param string
	 * @param string
	 * @throws Exception
	 */
	public function __construct($strTemplate='')
	{
		$this->strTemplate = $strTemplate;
	}

	/**
	 * Set an object property
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		$this->arrData[$strKey] = $varValue;
	}

	/**
	 * Return an object property
	 * @param string
	 * @return mixed
	 */
	public function __get($strKey)
	{
		return $this->arrData[$strKey];
	}

	/**
	 * Check whether a property is set
	 * @param string
	 * @return boolean
	 */
	public function __isset($strKey)
	{
		return isset($this->arrData[$strKey]);
	}

	/**
	 * Set the template data from an array
	 * @param array
	 */
	public function setData($arrData)
	{
		$this->arrData = $arrData;
	}

	/**
	 * Return the template data as array
	 * @return array
	 */
	public function getData()
	{
		return $this->arrData;
	}

	/**
	 * Set the template name
	 * @param string
	 */
	public function setName($strTemplate)
	{
		$this->strTemplate = $strTemplate;
	}

	/**
	 * Return the template name
	 * @return string
	 */
	public function getName()
	{
		return $this->strTemplate;
	}

	/**
	 * Print all template variables to the screen using print_r
	 */
	public function showTemplateVars()
	{
		echo "<pre>\n";
		print_r($this->arrData);
		echo "</pre>\n";
	}

	/**
	 * Print all template variables to the screen using var_dump
	 */
	public function dumpTemplateVars()
	{
		echo "<pre>\n";
		var_dump($this->arrData);
		echo "</pre>\n";
	}

	/**
	 * Find a particular template file and return its path
	 * @param string
	 * @param string
	 * @return string
	 * @throws Exception
	 */
	protected function getTemplate($strTemplate, $strFormat='html5', $blnFailIfNotFound = false)
	{
		try {
			return MetaModelTemplateLoader::getTemplateFile(basename($strTemplate), $strFormat);
		} catch(Exception $e) {
			if($blnFailIfNotFound) {
				throw $e;
			}
		}
	}

	protected function callParseTemplateHook()
	{
		if (isset($GLOBALS['METAMODEL_HOOKS']['parseTemplate']) && is_array($GLOBALS['METAMODEL_HOOKS']['parseTemplate']))
		{
			foreach ($GLOBALS['METAMODEL_HOOKS']['parseTemplate'] as $callback)
			{
				list($strClass, $strMethod) = $callback;
				$objCallback = (in_array('getInstance', get_class_methods($strClass)))
					? call_user_func(array($strClass, 'getInstance'))
					: new $strClass();

				$objCallback->$strMethod($this);
			}
		}
	}

	/**
	 * Parse the template file and return it as string
	 *
	 * @param $strOutputFormat   the desired output format.
	 *
	 * @param $blnFailIfNotFound if set to true, the template object will throw an exception if the template can not be found. Defaults to false.
	 *
	 * @return string the parsed template.
	 *
	 */
	public function parse($strOutputFormat, $blnFailIfNotFound = false)
	{
		if ($this->strTemplate == '')
		{
			return '';
		}

		// HOOK: add custom parse filters
		$this->callParseTemplateHook();

		$strTplFile = $this->getTemplate($this->strTemplate, $strOutputFormat, $blnFailIfNotFound);
		if ($strTplFile)
		{
			$this->strFormat = $strOutputFormat;

			ob_start();
			include($strTplFile);
			$strBuffer = ob_get_contents();
			ob_end_clean();

			$this->strFormat =  NULL;

			return $strBuffer;
		}
	}

	/**
	 * Protected as only the included template file shall be able to call as outside from {@link MetaModelTemplate::parse()} the format is undefined.
	 */
	protected function getFormat()
	{
		return $this->strFormat;
	}

	public static function render($strTemplate, $strOutputFormat, $arrTplData, $blnFailIfNotFound = false)
	{
		$objTemplate = new self($strTemplate);
		$objTemplate->setData($arrTplData);
		return $objTemplate->parse($strOutputFormat, $blnFailIfNotFound);
	}
}
