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
 * This class is used from DCA tl_metamodel for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class TableMetaModel extends Backend
{
	/**
	 * Class constructor, imports the Backend user.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	/**
	 * Creates or renames the MetaModel table according to the given name.
	 * Updates variant support information.
	 *
	 * @param DC_General $objDC the datacontainer where the model is loaded.
	 *
	 * @return void
	 */
	public function onSubmitCallback(DC_General $objDC)
	{

		// table name changed?
		$strOldTableName = '';
		if ($objDC->getId())
		{
			$objMetaModel = $this->Database->prepare("SELECT tableName FROM tl_metamodel WHERE id=?")
											->limit(1)
											->executeUncached($objDC->getId());
			if ($objMetaModel->numRows)
			{
				$strOldTableName = $objMetaModel->tableName;
			}
		}

		$objDBModel = $objDC->getCurrentModel();

		$strNewTableName = $objDBModel->getProperty('tableName');

		// table name is different.
		if ($strNewTableName != $strOldTableName)
		{
			if ($strOldTableName && $this->Database->tableExists($strOldTableName, null, true))
			{
				MetaModelTableManipulation::renameTable($strOldTableName, $strNewTableName);
				// TODO: notify fields that the MetaModel has changed its table name.
			} else {
				MetaModelTableManipulation::createTable($strNewTableName);
			}
		}
		MetaModelTableManipulation::setVariantSupport($strNewTableName, $objDBModel->getProperty('varsupport'));
	}

	/**
	 * Destroys the MetaModel table and all associated entries in child tables like filter-, render- and dcasettings.
	 *
	 * @param DC_General $objDC the datacontainer where the model is loaded.
	 *
	 * @return void
	 */
	public function onDeleteCallback(DC_General $objDC)
	{
		$objMetaModel = MetaModelFactory::byId($objDC->getId());
		if ($objMetaModel)
		{
			// TODO: implement IMetaModel*::suicide() to delete all entries in secondary tables (complex attributes), better than here in an callback.
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				$objAttribute->destroyAUX();
			}
			MetaModelTableManipulation::deleteTable($objMetaModel->getTableName());
			$this->Database->prepare('DELETE FROM tl_metamodel_attribute WHERE pid=?')
						   ->executeUncached($objMetaModel->get('id'));

			$this->Database->prepare('DELETE FROM tl_metamodel_dca_combine WHERE pid=?')
						   ->executeUncached($objMetaModel->get('id'));

			// delete everything from dca settings
			$arrIds = $this->Database->prepare('SELECT id FROM tl_metamodel_dca WHERE pid=?')
						   ->executeUncached($objMetaModel->get('id'))
						   ->fetchEach('id');
			if ($arrIds)
			{
				$this->Database->prepare(sprintf('DELETE FROM tl_metamodel_dcasetting WHERE pid IN (%s)', implode(',', $arrIds)))
							   ->executeUncached();
			}
			$this->Database->prepare('DELETE FROM tl_metamodel_dca WHERE pid=?')
						   ->executeUncached($objMetaModel->get('id'));

			// delete everything from render settings
			$arrIds = $this->Database->prepare('SELECT id FROM tl_metamodel_rendersettings WHERE pid=?')
						   ->executeUncached($objMetaModel->get('id'))
						   ->fetchEach('id');
			if ($arrIds)
			{
				$this->Database->prepare(sprintf('DELETE FROM tl_metamodel_rendersetting WHERE pid IN (%s)', implode(',', $arrIds)))
							   ->executeUncached();
			}
			$this->Database->prepare('DELETE FROM tl_metamodel_rendersettings WHERE pid=?')
						   ->executeUncached($objMetaModel->get('id'));

			// delete everything from filter settings
			$arrIds = $this->Database->prepare('SELECT id FROM tl_metamodel_filter WHERE pid=?')
						   ->executeUncached($objMetaModel->get('id'))
						   ->fetchEach('id');
			if ($arrIds)
			{
				$this->Database->prepare(sprintf('DELETE FROM tl_metamodel_filtersetting WHERE pid IN (%s)', implode(',', $arrIds)))
							   ->executeUncached();
			}
			$this->Database->prepare('DELETE FROM tl_metamodel_filter WHERE pid=?')
						   ->executeUncached($objMetaModel->get('id'));
		}
	}

	public function fixLangArray($varValue)
	{
		$arrLangValues = (array)deserialize($varValue);
		$arrOutput = array();
		foreach ($arrLangValues as $strLangCode => $varSubValue)
		{
			if (is_array($varSubValue))
			{
				$arrOutput[] = array_merge($varSubValue, array('langcode' => $strLangCode));
			}
		}
		return serialize($arrOutput);
	}

	public function unfixLangArray($varValue)
	{
		$arrLangValues = deserialize($varValue);
		$blnHaveFallback = false;
		$arrOutput = array();
		foreach ($arrLangValues as $varSubValue)
		{
			$strLangCode = $varSubValue['langcode'];
			unset($varSubValue['langcode']);

			// we clear all subsequent fallbacks after we have found one.
			if($blnHaveFallback)
			{
				$varSubValue['isfallback'] = '';
			}
			if($varSubValue['isfallback'])
			{
				$blnHaveFallback = true;
			}
			$arrOutput[$strLangCode] = $varSubValue;
		}

		// if no fallback has been set, use the first language available.
		if((!$blnHaveFallback) && count($arrOutput))
		{
			$arrOutput[$arrLangValues[0]['langcode']]['isfallback'] = '1';
		}

		return serialize($arrOutput);
	}

	/**
	 * Return the button if logged in as admin/user has the right to manipulate this MetaModel
	 *
	 * @param array  $arrRow        the current data row.
	 * @param string $strHref       the link to perform the action.
	 * @param string $strLabel      the label for the button.
	 * @param string $strTitle      the title for the button.
	 * @param string $strIcon       the icon image for the button.
	 * @param string $strAttributes additional attributes for the button.
	 *
	 * @return string the button link
	 */
	public function buttonCallback($arrRow, $strHref, $strLabel, $strTitle, $strIcon, $strAttributes)
	{
		if (!$this->User->isAdmin)
		{
			return '';
		}
		$strImg = $this->generateImage($strIcon, $strLabel);
		return sprintf('<a href="%s" title="%s"%s>%s</a> ',
			$this->addToUrl($strHref.'&amp;id='.$arrRow['id']),
			specialchars($strTitle),
			$strAttributes,
			$strImg?$strImg:$strLabel
		);
	}

	/**
	 * Return the button if logged in as admin/user has the right to manipulate this MetaModel
	 *
	 * @param array  $arrRow        the current data row.
	 * @param string $strHref       the link to perform the action.
	 * @param string $strLabel      the label for the button.
	 * @param string $strTitle      the title for the button.
	 * @param string $strIcon       the icon image for the button.
	 * @param string $strAttributes additional attributes for the button.
	 *
	 * @return string the button link
	 */
	public function buttonCallbackItemEdit($arrRow, $strHref, $strLabel, $strTitle, $strIcon, $strAttributes)
	{
		if (!$this->User->isAdmin)
		{
			return '';
		}
		return sprintf('<a href="%s" title="%s"%s>%s</a> ',
//			$this->addToUrl($strHref.'&amp;table='.$arrRow['tableName']),
			'contao/main.php?do=metamodel_' . $arrRow['tableName'],
			specialchars($strTitle),
			$strAttributes,
			$this->generateImage($strIcon, $strLabel)
		);
	}

	/**
	 * Render a row for the list view in the backend.
	 *
	 * @param array         $arrRow   the current data row.
	 * @param string        $strLabel the label text.
	 * @param DataContainer $objDC    the DataContainer instance that called the method.
	 */
	public function getRowLabel($arrRow, $strLabel, $objDC)
	{
		if(!($arrRow['tableName'] && $this->Database->tableExists($arrRow['tableName'], null, true)))
			return '';
		// add image
		$strImage = '';
		if ($arrRow['addImage'])
		{
			$arrSize = deserialize($arrRow['size']);
			$strImage = sprintf('<div class="image" style="padding-top:3px"><img src="%s" alt="%s" /></div> ',
				$this->getImage($arrRow['singleSRC'], $arrSize[0], $arrSize[1], $arrSize[2]),
				htmlspecialchars($strLabel)
			);
		}

		// count items
		$objCount = $this->Database->prepare("SELECT count(*) AS itemCount FROM ".$arrRow['tableName'])
					->execute();
		$itemCount =  sprintf(' <span style="color:#b3b3b3; padding-left:3px">[' . $GLOBALS['TL_LANG']['tl_metamodel']['itemFormat'] . ']</span>', $objCount->itemCount,
			($objCount->itemCount == 1) ? sprintf($GLOBALS['TL_LANG']['tl_metamodel']['itemSingle'])
										: sprintf($GLOBALS['TL_LANG']['tl_metamodel']['itemPlural'])
		);

		return '<span class="name">'.$strLabel. $itemCount . '</span>'.$strImage;
	}

	/**
	 * called by tl_metamodel.tableName onsave_callback.
	 * prefixes the table name with mm_ if not provided by the user as such.
	 * Checks if the table name is legal to the DB.
	 *
	 * @param string        $strTableName the table name for the table.
	 * @param DataContainer $objDC        the DataContainer which called us.
	 *
	 * @return string the table name $strTableName.
	 */
	public function tableNameOnSaveCallback($strTableName, DC_General $objDC)
	{
        // See #49
		$strTableName = strtolower($strTableName);

		// force mm_ prefix.
		if(substr($strTableName, 0, 3) !== 'mm_')
		{
			$strTableName = 'mm_' . $strTableName;
		}

		MetaModelTableManipulation::checkTablename($strTableName);

		return $strTableName;
	}

	public function getAttributes()
	{
		$objMetaModel = MetaModelFactory::byId();
		$tables = array();
		foreach($this->Database->listTables() as $table)
		{
			$tables[$table]=$table;
		}
		return $tables;
	}

	/**
	 * list all index fields with type int from a table
	 * @param DataContainer $dc
	 * @return array : string fieldname => string fieldname
	 */
	public function getTableKeys(DataContainer $dc)
	{
		// TODO: unused currently.
		$result = array();
		$objTable = $this->Database->prepare("SELECT itemTable FROM tl_metamodel WHERE id=?")
				->limit(1)
				->execute($dc->id);
		if ($objTable->numRows > 0
		    && $this->Database->tableExists($objTable->itemTable, null, true))
		{
			$fields = $this->Database->listFields($objTable->itemTable);
			foreach($fields as $field)
			{
				if(array_key_exists('index', $field) && $field['type'] == 'int')
					$result[$field['name']] = $field['name'];
			}

		}
		return $result;
	}
}

