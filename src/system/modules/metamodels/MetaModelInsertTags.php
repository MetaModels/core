<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package     MetaModels
 * @subpackage  metamodels_inserttags
 * @author      Tim Gatzky <info@tim-gatzky.de>
 * @author		Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright   The MetaModels team.
 * @license     LGPL.
 * @filesource
 */

/**
 * MetaModelsInserttag.
 * 
 * Available inserttags:
 * 
 * -- Total Count --
 * mm::total::mod::[id]
 * mm::total::ce::[id]
 * 
 * -- Item --
 * mm::item::[MM Name|ID]::[Item ID|ID,ID,ID]::[ID rendersetting](::[Output raw|text|html|..])
 * mm::detail::[MM Name|ID]::[Item ID]::[ID rendersetting](::[Output raw|text|html|..])
 * 
 * -- Atrribute --
 * mm::attribute::[MM Name|ID]::[Item ID]::[Attribute Name|ID](::[Output raw|text|html|..])
 * 
 * -- JumpTo --
 * mm::jumpTo::[MM Name|ID]::[Item ID]::[ID rendersetting](::[Parameter (Default:url)|label|page|params.attname])
 */
class MetaModelInsertTags extends Controller
{

	public function replaceTags($strTag)
	{
		$arrElements = explode('::', $strTag);

		// Check if we have the mm tags.
		if ($arrElements[0] != 'mm')
		{
			return false;
		}
		
		try
		{
			// Call the fitting function.
			switch ($arrElements[1])
			{
				// Count for mod or ce elements.
				case 'total':
					return $this->getCount($arrElements[2], $arrElements[3]);

				// Get value from an attribute.
				case 'attribute':
					return $this->getAttribute($arrElements[2], $arrElements[3], $arrElements[4], $arrElements[5]);

				// Get item.
				case 'item':
					return $this->getItem($arrElements[2], $arrElements[3], $arrElements[4]);
					
				case 'jumpTo':
					return $this->jumpTo($arrElements[2], $arrElements[3], $arrElements[4], $arrElements[5]);
			}
		}
		catch (Exception $exc)
		{
			$this->log('Error by replac tags: ' . $exc->getMessage(), __CLASS__ . ' | ' . __FUNCTION__, TL_ERROR);
		}
		
		return false;
	}

	////////////////////////////////////////////////////////////////////////////
	// Tag functions
	////////////////////////////////////////////////////////////////////////////

	/**
	 * Get the jumpTo for a chosen value.
	 * 
	 * @param string|int $mixMMName ID or name of MetaModels
	 * @param int $mixDataId ID of the data row
	 * @param int $intIdRendesetting ID of render setting
	 * @param string $strParam Name of parameter - Default:url|label|page|params.[attrname]
	 * 
	 * @return boolean|string Return false when nothing was found for the requested value.
	 */
	protected function jumpTo($mixMMName, $mixDataId, $intIdRendesetting, $strParam = "url")
	{
		// Get the MetaModel. Return if we can not find one.
		$objMetaModel = $this->loadMM($mixMMName);
		if ($objMetaModel == null)
		{
			return false;
		}

		// Get the rendersetting.
		$objRenderSettings = MetaModelRenderSettingsFactory::byId($objMetaModel, $intIdRendesetting);
		if ($objRenderSettings == null)
		{
			return false;
		}

		// Get the data row.
		$objItem = $objMetaModel->findById($mixDataId);
		if ($objItem == null)
		{
			return false;
		}

		// Render the item and check if we have a jump to.
		$arrRenderedItem = $objItem->parseValue('text', $objRenderSettings);
		if (!isset($arrRenderedItem['jumpTo']))
		{
			return false;
		}

		// Check if someone want the sub params.
		if (stripos($strParam, 'params.') !== false)
		{
			$mixAttName	 = trimsplit('.', $strParam);
			$mixAttName	 = array_pop($mixAttName);

			if (isset($arrRenderedItem['jumpTo']['params'][$mixAttName]))
			{
				return $arrRenderedItem['jumpTo']['params'][$mixAttName];
			}
		}
		// Else just return the ask param.
		else if (isset($arrRenderedItem['jumpTo'][$strParam]))
		{
			return $arrRenderedItem['jumpTo'][$strParam];
		}

		// Nothing hit the output. Return false.
		return false;
	}

	/**
	 * Get an item.
	 * 
	 * @param string|int $mixMMName ID or name of MetaModels
	 * @param int $mixDataId ID of the data row
	 * @param int $intIdRendesetting ID of render setting
	 * @param string $strOutput Name of output. Default:raw|text|html5|xhtml|...
	 * 
	 * @return boolean|string Return false when nothing was found or return the value.
	 */
	protected function getItem($mixMMName, $mixDataId, $intIdRendesetting, $strOutput = 'raw')
	{
		// Get the MetaModel. Return if we can not find one.
		$objMetaModel = $this->loadMM($mixMMName);
		if ($objMetaModel == null)
		{
			return false;
		}
		
		// Set output to default if not set.
		if(empty($strOutput))
		{
			$strOutput = 'raw';
		}

		$objMetaModelList = new MetaModelList();
		$objMetaModelList->setMetaModel($objMetaModel->get('id'), $intIdRendesetting);

		// handle a set of ids
		$arrIds = trimsplit(',', $mixDataId);

		// Check each id if published.
		foreach ($arrIds as $intKey => $intId)
		{
			if (!$this->isPublishedItem($objMetaModel, $intId))
			{
				unset($arrIds[$intKey]);
			}
		}

		// Render an empty inserttag rather than displaying a list with an empty 
		// result information. do not return false here because the inserttag itself is correct.
		if (count($arrIds) < 1)
		{
			return '';
		}

		$objMetaModelList->addFilterRule(new MetaModelFilterRuleStaticIdList($arrIds));
		return $objMetaModelList->render(false, $this);
	}

	/**
	 * Get from MM X the item with the id Y and parse the attribute Z and
	 * return it.
	 * 
	 * @param string|int $mixMMName ID or name of MetaModels
	 * @param int $intDataId ID of the data row
	 * @param string $strAttributeName Name of the attribute.
	 * @param string $strOutput Name of output. Default:raw|text|html5|xhtml|...
	 * 
	 * @return boolean|string Return false when nothing was found or return the value.
	 */
	protected function getAttribute($mixMMName, $intDataId, $strAttributeName, $strOutput = 'raw')
	{
		// Get the MM.
		$objMM = $this->loadMM($mixMMName);
		if ($objMM == null)
		{
			return false;
		}		
		
		// Set output to default if not set.
		if(empty($strOutput))
		{
			$strOutput = 'raw';
		}

		// Get item.
		$objMetaModelItem = $objMM->findById($intDataId);
		
		// Parse attribute.
		$arrAttr = $objMetaModelItem->parseAttribute($strAttributeName);

		// ToDo: Maybe this should not allways be a text element.
		return $arrAttr[$strOutput];
	}

	/**
	 * Get count from a module or content element of a mm.
	 * 
	 * @param string $strType Type of element like mod or ce.
	 * @param int $intID ID of content element or moule.
	 * 
	 * @return boolean|string Return false when nothing was found or the count value.
	 */
	protected function getCount($strType, $intID)
	{
		switch ($strType)
		{
			// From module, can be a metamodel list or filter
			case 'mod':
				$objMMResult = $this->getMMDataFrom('tl_module', $intID);
				break;

			// From content element, can be a metamodel list or filter.
			case 'ce':
				$objMMResult = $this->getMMDataFrom('tl_content', $intID);
				break;

			// Unknow element type.
			default:
				return false;
		}

		// Check if we have data
		if ($objMMResult != null)
		{
			return $this->getCountFor($objMMResult->metamodel, $objMMResult->metamodel_filtering);
		}

		return false;
	}

	////////////////////////////////////////////////////////////////////////////
	// Helper
	////////////////////////////////////////////////////////////////////////////

	/**
	 * Try to laod the mm by id or name.
	 * 
	 * @param mixed $mixMMName Name or id of mm.
	 * 
	 * @return IMetaModel|null
	 */
	protected function loadMM($mixMMName)
	{
		// ID.
		if (is_numeric($mixMMName))
		{
			return MetaModelFactory::byId($mixMMName);
		}
		// Name.
		else if (is_string($mixMMName))
		{
			return MetaModelFactory::byTableName($mixMMName);
		}

		// Unknown.
		return null;
	}

	/**
	 * Get the metamodel id and the filter id.
	 * 
	 * @param string $strTable Name of table
	 * 
	 * @return null|Database_Result Returns null when nothing was found or a 
	 * Contao Database_Result with the chosen informations.
	 */
	protected function getMMDataFrom($strTable, $intID)
	{
		$objDB = Database::getInstance();

		// Check if we know the table
		if (!$objDB->tableExists($strTable))
		{
			return null;
		}

		// Get all information form table or retunr null if we have no data.
		$objResult = $objDB
				->prepare("SELECT metamodel, metamodel_filtering FROM " . $strTable . " WHERE id=?")
				->limit(1)
				->execute($intID);

		// Check if we have some data.
		if ($objResult->numRows < 1)
		{
			return null;
		}

		return $objResult;
	}

	/**
	 * Get count form one MM for chosen filter.
	 * 
	 * @param int $intMMId ID of the metamodels
	 * @param int $intFilterID ID of the filter
	 * 
	 * @return boolean|int False for no data or integer for the count result.
	 */
	protected function getCountFor($intMMId, $intFilterID)
	{
		// ToDo: Add check if we have realy a mm and ff.
		$objMetaModel = $this->loadMM($intMMId);
		if ($objMetaModel == null)
		{
			return false;
		}

		$objFilter = $objMetaModel->prepareFilter($intFilterID, $_GET);

		return $objMetaModel->getCount($objFilter);
	}

	/**
	 * Check if the item is published.
	 * 
	 * @param IMetaModel $objMetaModel Current metamodels.
	 * @param int $intItemId Id of the item.
	 * 
	 * @return boolean True => Published | Flase => Not published
	 */
	protected function isPublishedItem($objMetaModel, $intItemId)
	{
		// check publish state of item
		$objAttrCheckPublish = Database::getInstance()
				->prepare("SELECT colname FROM tl_metamodel_attribute WHERE pid=? AND check_publish=1")
				->limit(1)
				->execute($objMetaModel->get('id'));

		if ($objAttrCheckPublish->numRows > 0)
		{
			$objItem = $objMetaModel->findById($intItemId);
			if (!$objItem->get($objAttrCheckPublish->colname))
			{
				return false;
			}
		}

		return true;
	}

}