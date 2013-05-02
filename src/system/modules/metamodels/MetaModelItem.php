<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Interface for a MetaModel item.
 *
 * @package	   MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelItem implements IMetaModelItem
{
	/**
	 * Name of the model this item originates from.
	 *
	 * @var string
	 */
	protected $strModelName = null;

	// TODO: switch to stdClass here?
	/**
	 * The data array containing the raw values obtained from the attributes.
	 *
	 * @var array
	 */
	protected $arrData = array();

	/**
	 * Create a new instance.
	 *
	 * @param IMetaModel $objMetaModel The model this item is represented by.
	 *
	 * @param array      $arrData      The initial data that shall be injected into the new instance.
	 */
	public function __construct(IMetaModel $objMetaModel, $arrData)
	{
		$this->arrData      = $arrData;
		$this->strModelName = $objMetaModel->getTableName();
	}

	/**
	 * Helper function for {@see MetaModelItem::parseValue()} and {@see MetaModelItem::parseAttribute()}.
	 *
	 * @param IMetaModelAttribute      $objAttribute    The attribute to parse.
	 *
	 * @param string                   $strOutputFormat The desired output format.
	 *
	 * @param IMetaModelRenderSettings $objSettings     The settings object to be applied.
	 *
	 * @return The parsed information for the given attribute.
	 */
	public function internalParseAttribute($objAttribute, $strOutputFormat, $objSettings)
	{
		$arrResult = array();
		if ($objAttribute)
		{
			// Extract view settings for this attribute.
			if ($objSettings)
			{
				$objAttributeSettings = $objSettings->getSetting($objAttribute->getColName());
			}
			else
			{
				$objAttributeSettings = null;
			}
			foreach ($objAttribute->parseValue($this->arrData, $strOutputFormat, $objAttributeSettings) as $strKey => $varValue)
			{
				$arrResult[$strKey] = $varValue;
			}
			// TODO: Add parseValue HOOK?
		}
		
		// If "hideEmptyValues" is true and the raw is empty remove text and outputformat.
		if($objSettings->get('hideEmptyValues') == true && $this->isEmptyValue($arrResult['raw']) == true)
		{
			unset($arrResult[$strOutputFormat]);
			unset($arrResult['text']);
		}
		
		return $arrResult;
	}

	/**
	 * Check if a value is empty
	 * 
	 * @param array $mixValue
	 * 
	 * @return boolean True => empty, false => found a valid values
	 */
	protected function isEmptyValue($mixValue)
	{
		// Array check
		if (is_array($mixValue))
		{		
			if(count($mixValue) == 0 || $this->isArrayEmpty($mixValue))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		// Empty string
		if ($mixValue === '')
		{
			return true;
		}

		// Null
		if ($mixValue === null)
		{
			return true;
		}

		return false;
	}

	/**
	 * Run through each level of an array and check if we have an empty value.
	 * 
	 * @param array $arrArray
	 * 
	 * @return boolean True => empty, False => some values found.
	 */
	protected function isArrayEmpty($arrArray)
	{
		if (is_array($arrArray))
		{
			foreach ($arrArray as $key => $value)
			{
				if (is_array($value) && !$this->isArrayEmpty($value))
				{
					return false;
				}

				if ($value !== '' && $value !== null)
				{
					return false;
				}
			}
		}
		else if ($value !== '' && $value !== null)
		{
			return false;
		}
		
		return true;
	}
		

	/**
	 * Return the native value of an attibute.
	 *
	 * @param string $strAttributeName The name of the attribute.
	 *
	 * @return mixed
	 */
	public function get($strAttributeName)
	{
		return $this->arrData[$strAttributeName];
	}

	/**
	 * Set the native value of an Attibute.
	 *
	 * @param string $strAttributeName The name of the attribute.
	 *
	 * @param mixed  $varValue         The value of the attribute.
	 *
	 * @return IMetaModelItem
	 */
	public function set($strAttributeName, $varValue)
	{
		$this->arrData[$strAttributeName] = $varValue;

		return $this;
	}

	/**
	 * Fetch the MetaModel that this item is originating from.
	 *
	 * @return IMetaModel the instance.
	 */
	public function getMetaModel()
	{
		return MetaModelFactory::byTableName($this->strModelName);
	}

	/**
	 * Fetch the MetaModel attribute instance with the given name.
	 *
	 * @param string $strAttributeName The name of the attribute.
	 *
	 * @return IMetaModelAttribute The instance.
	 */
	public function getAttribute($strAttributeName)
	{
		return $this->getMetaModel()->getAttribute($strAttributeName);
	}

	/**
	 * Determines if this item is a variant of another item.
	 *
	 * @return bool True if it is an variant, false otherwise.
	 */
	public function isVariant()
	{
		return $this->getMetaModel()->hasVariants() && ($this->arrData['varbase'] === '0');
	}

	/**
	 * Determines if this item is variant base of other items.
	 *
	 * Note: this does not mean that there actually exist variants of
	 * this item. It merely simply states, that this item is able
	 * to function as variant base for other items.
	 *
	 * @return bool True if it is an variant base, false otherwise.
	 */
	public function isVariantBase()
	{
		return $this->getMetaModel()->hasVariants() && ($this->arrData['varbase'] === '1');
	}

	/**
	 * Fetch the meta model variants for this item.
	 *
	 * @param IMetaModelFilter $objFilter The filter settings to be applied.
	 *
	 * @return IMetaModelItems A list of all variants for this item.
	 */
	public function getVariants($objFilter)
	{
		if ($this->isVariantBase())
		{
			return $this->getMetaModel()->findVariants(array($this->get('id')), $objFilter);
		}

		return null;
	}

	/**
	 * Find all Variants including the variant base.
	 *
	 * The item itself is excluded from the return list.
	 *
	 * @param type $objFilter The additional filter settings to apply.
	 *
	 * @return null|IMetaModelItems
	 */
	public function getSiblings($objFilter)
	{
		if (!$this->getMetaModel()->hasVariants())
		{
			return null;
		}
		return $this->getMetaModel()->findVariantsWithBase(array($this->get('id')), $objFilter);

	}

	/**
	 * Save the current data for every attribute to the data sink.
	 *
	 * @return void
	 */
	public function save()
	{
		$objMetaModel = $this->getMetaModel();
		$objMetaModel->saveItem($this);
	}

	/**
	 * Renders the item in the given output format.
	 *
	 * @param string                   $strOutputFormat The desired output format (optional - default: text).
	 *
	 * @param IMetaModelRenderSettings $objSettings     The render settings to use (optional - default: null).
	 *
	 * @return array attribute name => format => value
	 */
	public function parseValue($strOutputFormat = 'text', $objSettings = null)
	{
		$arrResult = array
		(
			'raw' => $this->arrData,
			'text' => array(),
			'attributes' => array(),
			$strOutputFormat => array()
		);

		// No render settings, parse "normal" and hope the best - not all attribute types must provide usable output.
		if (!$objSettings)
		{
			foreach ($this->getMetaModel()->getAttributes() as $objAttribute)
			{
				$arrResult['attributes'][$objAttribute->getColName()] = $objAttribute->getName();
				foreach ($this->internalParseAttribute($objAttribute, $strOutputFormat, null) as $strKey => $varValue)
				{
					$arrResult[$strKey][$objAttribute->getColName()] = $varValue;
				}
			}
			return $arrResult;
		}

		// First, parse the values in the same order as they are in the render settings.
		foreach ($objSettings->getSettingNames() as $strAttrName)
		{
			$objAttribute = $this->getMetaModel()->getAttribute($strAttrName);
			if ($objAttribute)
			{
				$arrResult['attributes'][$objAttribute->getColName()] = $objAttribute->getName();
				foreach ($this->internalParseAttribute($objAttribute, $strOutputFormat, $objSettings) as $strKey => $varValue)
				{
					$arrResult[$strKey][$objAttribute->getColName()] = $varValue;
				}
			}
		}

		$arrResult['jumpTo'] = $this->buildJumpToLink($objSettings);

		// Call HOOK for other extensions to inject data.
		$this->parseValueHook($arrResult, $strOutputFormat, $objSettings);

		return $arrResult;
	}

	/**
	 * HOOK handler for third party extensions to inject data into the generated output or to reformat the output.
	 *
	 * @param array                    &$arrResult  The generated data.
	 *
	 * @param string                   $strFormat   The desired output format
	 *                                              (text, html, etc.).
	 *
	 * @param IMetaModelRenderSettings $objSettings The render settings to use.
	 *
	 * @return void
	 */
	protected function parseValueHook(&$arrResult, $strFormat, $objSettings)
	{
		// HOOK: let third party extensions manipulate the generated data.
		if (is_array($GLOBALS['METAMODEL_HOOKS']['MetaModelItem::parseValue'])
			&& count($GLOBALS['METAMODEL_HOOKS']['MetaModelItem::parseValue'])
		)
		{
			foreach ($GLOBALS['METAMODEL_HOOKS']['MetaModelItem::parseValue'] as $arrHook)
			{
				$strClass  = $arrHook[0];
				$strMethod = $arrHook[1];

				if (in_array('getInstance', get_class_methods($strClass)))
				{
					$objHook = call_user_func(array($strClass, 'getInstance'));
				} else {
					$objHook = new $strClass();
				}
				$objHook->$strMethod($arrResult, $this, $strFormat, $objSettings);
			}
		}
	}

	/**
	 * Build the jumpTo link for use in templates.
	 *
	 * The returning array will hold the following keys:
	 * * params - the url parameter (only if a valid filter setting could be determined).
	 * * deep   - boolean true, if parameters are non empty, false otherwise.
	 * * page   - id of the jumpTo page.
	 * * url    - the complete generated url
	 *
	 * @param IMetaModelRenderSettings $objSettings The render settings to use.
	 *
	 * @return array
	 */
	public function buildJumpToLink($objSettings)
	{
		if (!$objSettings)
		{
			return null;
		}

		// Get the right jumpto.
		$strDesiredLanguage  = $this->getMetaModel()->getActiveLanguage();
		$strFallbackLanguage = $this->getMetaModel()->getFallbackLanguage();

		foreach ((array)$objSettings->get('jumpTo') as $arrJumpTo)
		{
			// If either desired language or fallback, keep the result.
			if (!$this->getMetaModel()->isTranslated()
				|| $arrJumpTo['langcode'] == $strDesiredLanguage
				|| $arrJumpTo['langcode'] == $strFallbackLanguage)
			{
				$intJumpTo         = $arrJumpTo['value'];
				$intFilterSettings = $arrJumpTo['filter'];
				// If the desired language, break. Otherwise try to get the desired one until all have been evaluated.
				if ($strDesiredLanguage == $arrJumpTo['langcode'])
				{
					break;
				}
			}
		}

		// Apply jumpTo urls based upon the filter defined in the render settings.
		$objPage = MetaModelController::getPageDetails($intJumpTo);
		if (!$objPage)
		{
			return null;
		}

		$arrJumpTo = array();

		if ($intFilterSettings)
		{
			$objFilterSettings = MetaModelFilterSettingsFactory::byId($intFilterSettings);
			$arrParams         = $objFilterSettings->generateFilterUrlFrom($this, $objSettings);

			foreach ($arrParams as $strKey => $strValue)
			{
				if ($strKey == 'auto_item')
				{
					$strParams = '/' . $strValue . $strParams;
				} else {
					$strParams .= sprintf('/%s/%s', $strKey, $strValue);
				}
			}

			$arrJumpTo['params'] = $arrParams;
			$arrJumpTo['deep']   = (strlen($strParams) > 0);
		}

		$arrJumpTo['page'] = $intJumpTo;
		$arrJumpTo['url']  = MetaModelController::generateFrontendUrl($objPage->row(), $strParams);
		return $arrJumpTo;
	}

	/**
	 * Renders a single attribute in the given output format.
	 *
	 * @param string                   $strAttributeName The desired attribute.
	 *
	 * @param string                   $strOutputFormat  The desired output format (optional - default: text).
	 *
	 * @param IMetaModelRenderSettings $objSettings      The render settings to use (optional - default: null).
	 *
	 * @return array format=>value
	 */
	public function parseAttribute($strAttributeName, $strOutputFormat = 'text', $objSettings = null)
	{
		return $this->internalParseAttribute($this->getAttribute($strAttributeName), $strOutputFormat, $objSettings);
	}

	/**
	 * Returns a new item containing the same values as this item but no id.
	 *
	 * This is useful when creating new items that shall be based upon anothe item.
	 *
	 * @return IMetaModelItem the new copy.
	 */
	public function copy()
	{
		// Fetch data, clean undesired fields and return the new item.
		$arrNewData = $this->arrData;
		unset($arrNewData['id']);
		unset($arrNewData['tstamp']);
		return new MetaModelItem($this->getMetaModel(), $arrNewData);
	}

	/**
	 * Returns a new item containing the same values as this item but no id.
	 *
	 * Additionally, the item will be a variant child of this item.
	 *
	 * NOTE: if this item is not a variant base itself, this item will return a item
	 * that is a child of this items variant base. i.e. excact clone.
	 *
	 * @return IMetaModelItem the new copy.
	 */
	public function varCopy()
	{
		$objNewItem = $this->copy();
		// If this item is a variant base, we need to clean the varbase and set
		// ourselves as the base.
		if ($this->isVariantBase())
		{
			$objNewItem->set('vargroup', $this->get('id'));
			$objNewItem->set('varbase', 0);
		} else {
			$objNewItem->set('vargroup', $this->get('vargroup'));
			$objNewItem->set('varbase', 0);
		}
		return $objNewItem;
	}
}

