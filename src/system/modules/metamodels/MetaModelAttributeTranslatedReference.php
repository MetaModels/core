<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * This is the MetaModelAttribute class for handling translated attributes that reference another table.
 *
 * @package     MetaModels
 * @subpackage  AttributeText
 * @author      Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelAttributeTranslatedReference
extends MetaModelAttributeComplex
implements IMetaModelAttributeTranslated
{

	protected function getValueTable()
	{
		throw new Exception('Error: value table not defined for ' . __CLASS__);
	}

	/**
	 * Build a where clause for the given id(s) and langcode.
	 *
	 * @param mixed  $mixIds      one, none or many ids to use.
	 *
	 * @param string $strLangCode the langcode to use, optional.
	 */
	protected function getWhere($mixIds, $strLangCode='')
	{
		$strWhereIds = '';
		if ($mixIds)
		{
			if (is_array($mixIds))
			{
				$strWhereIds = ' AND item_id IN (' . implode(',', $mixIds) . ')';
			} else {
				$strWhereIds = ' AND item_id='. $mixIds;
			}
		}
		$arrReturn = array(
			'procedure' => 'att_id=?' . $strWhereIds,
			'params' => array(intval($this->get('id')))
		);
		if ($strLangCode)
		{
			$arrReturn['procedure'] .=  ' AND langcode=?';
			$arrReturn['params'][] = $strLangCode;
		}

		return $arrReturn;
	}

	protected function getSetValues($arrValue, $intId, $strLangCode)
	{
		return array
		(
			'tstamp' => time(),
			'value' => (string)$arrValue['value'],
			'att_id' => $this->get('id'),
			'langcode' => $strLangCode,
			'item_id' => $intId,
		);
	}

	protected function getOptionizer()
	{
		return array(
			'key' => 'value',
			'value' => 'value'
		);
	}


	public function valueToWidget($varValue)
	{
		return $varValue['value'];
	}

	public function widgetToValue($varValue, $intId)
	{
		return array
		(
			'tstamp' => time(),
			'value' => $varValue,
			'att_id' => $this->get('id'),
//			'langcode' => $strLangCode,
//			'item_id' => $intId,
		);
	}

	public function getDataFor($arrIds/*, array $arrResult*/) // TODO see interface comment
	{
		$strActiveLanguage = $this->getMetaModel()->getActiveLanguage();
		$strFallbackLanguage = $this->getMetaModel()->getFallbackLanguage();

		$arrReturn = $this->getTranslatedDataFor($arrIds, $strActiveLanguage);

		// second round, fetch fallback languages if not all items could be resolved.
		if ((count($arrReturn) < count($arrIds)) && ($strActiveLanguage != $strFallbackLanguage))
		{
			$arrFallbackIds = array();
			foreach ($arrIds as $intId)
			{
				if (empty($arrReturn[$intId]))
				{
					$arrFallbackIds[] = $intId;
				}
			}

			if ($arrFallbackIds)
			{
				$arrFallbackData = $this->getTranslatedDataFor($arrFallbackIds, $strFallbackLanguage);
				// cannot use array_merge here as it would renumber the keys.
				foreach ($arrFallbackData as $intId => $arrValue)
				{
					$arrReturn[$intId] = $arrValue;
				}
			}
		}
		return $arrReturn;
	}

	public function setDataFor($arrValues/*, $objItem*/) // TODO see interface comment
	{
		foreach ($this->getMetaModel()->getAvailableLanguages() as $strLangCode)
		{
			$this->setTranslatedDataFor($arrValues, $strLangCode);
		}
	}

	public function unsetDataFor($arrIds)
	{
		foreach ($this->getMetaModel()->getAvailableLanguages() as $strLangCode)
		{
			$this->unsetValueFor($arrIds, $strLangCode);
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * Search the attribute values for the given pattern in the active language.
	 */
	public function searchFor($strPattern)
	{
		return $this->searchForInLanguages($strPattern, array($this->getMetaModel()->getActiveLanguage()));
	}

	/**
	 * {@inheritdoc}
	 *
	 * Search the attribute in the given languages.
	 */
	public function searchForInLanguages($strPattern, $arrLanguages = array())
	{
		$arrWhere = $this->getWhere(null);
		$arrParams = array(str_replace(array('*', '?'), array('%', '_'), $strPattern));

		$arrOptionizer = $this->getOptionizer();

		if ($arrWhere)
		{
			$arrParams = array_merge($arrParams, $arrWhere['params']);
		}

		$objFilterRule = new MetaModelFilterRuleSimpleQuery(
			sprintf(
				'SELECT DISTINCT %s FROM %s WHERE %s LIKE ? %s%s',
				'item_id',
				$this->getValueTable(),
				$arrOptionizer['value'],
				($arrWhere ? ' AND ' . $arrWhere['procedure'] : ''),
				$arrLanguages ? sprintf(' AND langcode IN (\'%s\')', implode('\',\'', $arrLanguages)) : ''
			),
			$arrParams,
			'item_id'
		);

		return $objFilterRule->getMatchingIds();
	}

	/**
	 * {@inheritdoc}
	 *
	 * Fetch filter options from foreign table.
	 *
	 */
	public function getFilterOptions($arrIds, $usedOnly)
	{
		$objDB = Database::getInstance();
		// TODO: implement $arrIds and $usedOnly handling here.

		$arrWhere = $this->getWhere($arrIds, $this->getMetaModel()->getActiveLanguage());
		$strQuery = 'SELECT * FROM ' . $this->getValueTable() . ($arrWhere ? ' WHERE ' . $arrWhere['procedure'] : '');

		$objValue = $objDB->prepare($strQuery)
						  ->execute(($arrWhere ? $arrWhere['params'] : null));

		$arrOptionizer = $this->getOptionizer();
		$arrReturn = array();
		while ($objValue->next())
		{
			$arrReturn[$objValue->$arrOptionizer['key']] = $objValue->$arrOptionizer['value'];
		}
		return $arrReturn;
	}

	/////////////////////////////////////////////////////////////////
	// interface IMetaModelAttributeTranslated
	/////////////////////////////////////////////////////////////////

	public function setTranslatedDataFor($arrValues, $strLangCode)
	{
		$objDB = Database::getInstance();
		// first off determine those to be updated and those to be inserted.
		$arrIds = array_keys($arrValues);
		$arrExisting = array_keys($this->getTranslatedDataFor($arrIds, $strLangCode));
		$arrNewIds = array_diff($arrIds, $arrExisting);

		// now update...
		$strQuery = 'UPDATE ' . $this->getValueTable() . ' %s';
		foreach ($arrExisting as $intId)
		{
			$arrWhere = $this->getWhere($intId, $strLangCode);
			$objDB->prepare($strQuery . ($arrWhere ? ' WHERE ' . $arrWhere['procedure'] : ''))
				  ->set($this->getSetValues($arrValues[$intId], $intId, $strLangCode))
				  ->execute(($arrWhere ? $arrWhere['params'] : null));
		}
		// ...and insert
		$strQuery = 'INSERT INTO ' . $this->getValueTable() . ' %s';
		foreach ($arrNewIds as $intId)
		{
			$objDB->prepare($strQuery)
				  ->set($this->getSetValues($arrValues[$intId], $intId, $strLangCode))
				  ->execute();
		}
	}

	/**
	 * Get values for the given items in a certain language.
	 */
	public function getTranslatedDataFor($arrIds, $strLangCode)
	{
		$objDB = Database::getInstance();

		$arrWhere = $this->getWhere($arrIds, $strLangCode);
		$strQuery = 'SELECT * FROM ' . $this->getValueTable() . ($arrWhere ? ' WHERE ' . $arrWhere['procedure'] : '');

		$objValue = $objDB->prepare($strQuery)
						  ->execute(($arrWhere ? $arrWhere['params'] : null));

		$arrReturn = array();
		while ($objValue->next())
		{
			$arrReturn[$objValue->item_id] = $objValue->row();
		}
		return $arrReturn;
	}

	/**
	 * Remove values for items in a certain lanugage.
	 */
	public function unsetValueFor($arrIds, $strLangCode)
	{
		$objDB = Database::getInstance();

		$arrWhere = $this->getWhere($arrIds, $strLangCode);
		$strQuery = 'DELETE FROM ' . $this->getValueTable() . ($arrWhere ? ' WHERE ' . $arrWhere['procedure'] : '');

		$objDB->prepare($strQuery)
			  ->execute(($arrWhere ? $arrWhere['params'] : null));
	}
}

