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
	protected $strModelName = NULL;

	// TODO: switch to stdClass here?
	protected $arrData = array();

	/**
	 * Create a new instance.

	 *
	 * @param IMetaModel $objMetaModel the model this item is represented by.

	 *
	 * @param array      $arrData      the initial data that shall be injected into the new instance.

	 *
	 * @return IMetaModelItem the instance
	 */
	public function __construct(IMetaModel $objMetaModel, $arrData)
	{
		$this->arrData = $arrData;
		$this->strModelName = $objMetaModel->getTableName();
	}

	/**
	 * helper function for {@see MetaModelItem::parseValue()} and {@see MetaModelItem::parseAttribute()}

	 *
	 * @param IMetaModelAttribute      $objAttribute    the attribute to parse.

	 *
	 * @param string                   $strOutputFormat the desired output format.

	 *
	 * @param IMetaModelRenderSettings $objSettings     the settings object to be applied.

	 *
	 */
	public function internalParseAttribute($objAttribute, $strOutputFormat, $objSettings)
	{
		$arrResult = array();
		if ($objAttribute)
		{
			// extract view settings for this attribute.
			if($objSettings)
			{
				$objAttributeSettings = $objSettings->getSetting($objAttribute->getColName());
			}
			else
			{
				$objAttributeSettings = NULL;
			}
			foreach($objAttribute->parseValue($this->arrData, $strOutputFormat, $objAttributeSettings) as $strKey => $varValue)
			{
				$arrResult[$strKey] = $varValue;
			}
			// TODO: parseValue HOOK?
		}
		return $arrResult;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($strAttributeName)
	{
		return $this->arrData[$strAttributeName];
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($strAttributeName, $varValue)
	{
		$this->arrData[$strAttributeName] = $varValue;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMetaModel()
	{
		return MetaModelFactory::byTableName($this->strModelName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAttribute($strAttributeName)
	{
		return $this->getMetaModel()->getAttribute($strAttributeName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isVariant()
	{
		return $this->getMetaModel()->hasVariants() && ($this->arrData['varbase'] === '0');
	}

	/**
	 * {@inheritdoc}
	 */
	public function isVariantBase()
	{
		return $this->getMetaModel()->hasVariants() && ($this->arrData['varbase'] === '1');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getVariants($objFilter)
	{
		if($this->isVariantBase())
		{
			return $this->getMetaModel()->findVariants(array($this->get('id')), $objFilter);
		} else {
			return null;
		}
	}

	/**
	* Find all Variants including the variant base. The item itself is excluded from the return list.
	*
	* @param type $objFilter
	* @return null
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
	 * {@inheritdoc}
	 */
	public function save()
	{
		$objMetaModel = $this->getMetaModel();
		$objMetaModel->saveItem($this);
	}

	/**
	 * {@inheritdoc}
	 */
	public function parseValue($strOutputFormat = 'text', $objSettings = NULL)
	{
		$arrResult = array
		(
			'raw' => $this->arrData,
			'text' => array(),
			'attributes' => array(),
			$strOutputFormat => array()
		);

		// no render settings, parse "normal" and hope the best - not all attribute types must provide usable output.
		if (!$objSettings)
		{
			foreach($this->getMetaModel()->getAttributes() as $objAttribute)
			{
				$arrResult['attributes'][$objAttribute->getColName()] = $objAttribute->getName();
				foreach($this->internalParseAttribute($objAttribute, $strOutputFormat, null) as $strKey => $varValue)
				{
					$arrResult[$strKey][$objAttribute->getColName()] = $varValue;
				}
			}
			return $arrResult;
		}

		// first, parse the values in the same order as they are in the render settings.
		foreach ($objSettings->getSettingNames() as $strAttrName)
		{
			$objAttribute = $this->getMetaModel()->getAttribute($strAttrName);
			if ($objAttribute)
			{
				$arrResult['attributes'][$objAttribute->getColName()] = $objAttribute->getName();
				foreach($this->internalParseAttribute($objAttribute, $strOutputFormat, $objSettings) as $strKey => $varValue)
				{
					$arrResult[$strKey][$objAttribute->getColName()] = $varValue;
				}
			}
		}
		
		$arrResult['jumpTo'] = $this->buildJumpToLink($objSettings);
		
		return $arrResult;
	}
	
	public function buildJumpToLink($objSettings) {
		if(!$objSettings) {
			return null;
		}
	
		//get the right jumpto
		$strDesiredLanguage = $this->getMetaModel()->getActiveLanguage();
		$strFallbackLanguage = $this->getMetaModel()->getFallbackLanguage();
		foreach((array) $objSettings->get('jumpTo') as $arrJumpTo)
		{
			// if either desired language or fallback, keep the result.
			if(!$this->getMetaModel()->isTranslated()
				|| $arrJumpTo['langcode'] == $strDesiredLanguage
				|| $arrJumpTo['langcode'] == $strFallbackLanguage)
			{
				$intJumpTo = $arrJumpTo['value'];
				$intFilterSettings = $arrJumpTo['filter'];
				// if the desired language, break. Otherwise try to get the desired one until all have been evaluated.
				if ($strDesiredLanguage == $arrJumpTo['langcode'])
				{
					break;
				}
			}
		}
	
		// second, apply jumpTo urls based upon the filter defined in the render settings.
		$objPage = MetaModelController::getPageDetails($intJumpTo);
		if(!$objPage) {
			return null;
		}
	
		$arrJumpTo = array();
	
		if($intFilterSettings) {
			$objFilterSettings = MetaModelFilterSettingsFactory::byId($intFilterSettings);
			$arrParams = $objFilterSettings->generateFilterUrlFrom($this, $objSettings);
				
			foreach ($arrParams as $strKey => $strValue)
			{
				if($strKey == 'auto_item') {
					$strParams = '/' . $strValue . $strParams;
				} else {
					$strParams .= sprintf('/%s/%s', $strKey, $strValue);
				}
			}
				
			$arrJumpTo['params'] = $arrParams;
			$arrJumpTo['deep'] = strlen($strParams) > 0;
		}
	
		$arrJumpTo['page'] = $intJumpTo;
		$arrJumpTo['url'] = MetaModelController::generateFrontendUrl($objPage->row(), $strParams);
		return $arrJumpTo;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function parseAttribute($strAttributeName, $strOutputFormat = 'text', $objSettings = NULL)
	{
		return $this->internalParseAttribute($this->getAttribute($strAttributeName), $strOutputFormat, $objSettings);
	}

	/**
	 * {@inheritdoc}
	 */
	public function copy()
	{
		// fetch data, clean undesired fields and return the new item.
		$arrNewData = $this->arrData;
		unset($arrNewData['id']);
		unset($arrNewData['tstamp']);
		return new MetaModelItem($this->getMetaModel(), $arrNewData);
	}

	/**
	 * {@inheritdoc}
	 */
	public function varCopy()
	{
		$objNewItem = $this->copy();
		// if this item is a variant base, we need to clean the varbase and set
		// ourselves as the base
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

