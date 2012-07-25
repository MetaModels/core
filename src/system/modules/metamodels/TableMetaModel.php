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
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * This class is used from DCA tl_metamodel for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class TableMetaModel extends Backend
{
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	public function onLoadCallback(DataContainer $objDC)
	{
		MetaModelPermissions::checkPermission($this->User, $this->Input->get('act'), $this->Input->get('id'));
		$this->checkRemoveTable($objDC);
	}

	/**
	 * Check if all dependencies are present.
	 */
	public function checkDependencies($strBuffer, $strTemplate)
	{
		if ($this->Input->get('do') != 'metamodel')
		{
			return $strBuffer;
		}

		$arrMissing = array();

		$arrActiveModules = $this->Config->getActiveModules();
		$arrInactiveModules = deserialize($GLOBALS['TL_CONFIG']['inactiveModules']);

		// check if all prerequsities are met.
		foreach($GLOBALS['METAMODELS']['dependencies'] as $strExtension => $strDisplay)
		{
			if (!in_array($strExtension, $arrActiveModules))
			{
				if (in_array($strExtension, $arrInactiveModules))
				{
					$arrMissing[] = sprintf('<li>Please activate required extension &quot;%s&quot; (%s)</li>', $strDisplay, $strExtension);
				} else {
					$arrMissing[] = sprintf('<li>Please install required extension &quot;%s&quot; (%s)</li>', $strDisplay, $strExtension);
				}
			}
		}

		if ($arrMissing)
		{
			if(preg_match('#<div id="main">(.*)<div class="clear">#ims', $strBuffer, $arrMatch))
			{
				$strBuffer = str_replace($arrMatch[1], sprintf('<div class="tl_gerror"><ul>%s</ul></div>', implode('', $arrMissing)), $strBuffer);
			}
		}
		return $strBuffer;
	}

	public function onSubmitCallback(DataContainer $objDC)
	{
		if($objDC->activeRecord)
		{
			MetaModelTableManipulation::setVariantSupport($objDC->activeRecord->tableName, $objDC->activeRecord->varsupport);
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

	protected function checkRemoveTable(DataContainer $dc)
	{
		return; // temporarily a no-op as unfinished.

		// TODO: When and where do we really need this function? IMO it is way too hazardous. See comment for bugfix below.

		// bugfix to resolve issue #52 also here - We keep ending up here as this is called from DC_Table::__construct
		// This means, when we are deleting comments (or whatever we might want to add in the future) the act equals 'delete'
		// and therefore without this check here, we would kill the MetaModel table.
		// We have to find out if this routine is really needed in this way, or if it would be better to handle it in something
		// like an onDelete callback. (c.schiffler 2009-08-04)
		if($this->Input->get('key') != '')
			return;
		$act = $this->Input->get('act');
		if ($act == 'deleteAll' || $act == 'delete')
		{
			if ($act == 'delete')
			{
				$ids = array($dc->id);
			}
			else
			{
				$session = $this->Session->getData();
				$ids = $session['CURRENT']['IDS'];
			}
			// TODO: Build a MetaModel::vaporize function for this or something like that.
			$objType = $this->Database->execute(
					sprintf("SELECT tableName FROM tl_metamodel WHERE id IN (%s)",
							implode(',', $ids)));

			while ($objType->next())
			{
				$tableName = $objType->tableName;

				if ($this->Database->tableExists($tableName, null, true))
				{
					$this->import('MetaModel');
					$this->MetaModel->dropTable($tableName);
				}
			}
		}
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
		$itemCount =  sprintf($GLOBALS['TL_LANG']['tl_metamodel']['itemFormat'], $objCount->itemCount,
			($objCount->itemCount == 1) ? sprintf($GLOBALS['TL_LANG']['tl_metamodel']['itemSingle'])
										: sprintf($GLOBALS['TL_LANG']['tl_metamodel']['itemPlural'])
		);

		return '<span class="name">'.$strLabel. $itemCount . '</span>'.$strImage;
	}

	/**
	 * called by tl_metamodel.tableName onsave_callback.
	 * Creates or renames the MetaModel table according to the given name.
	 *
	 * @param string        $strTableName the table name for the table.
	 * @param DataContainer $objDC        the DataContainer which called us.
	 *
	 * @return string the table name $strTableName.
	 */
	public function tableNameOnSaveCallback($strTableName, DataContainer $objDC)
	{
		// force mm_ prefix.
		if(substr($strTableName, 0, 3) !== 'mm_')
		{
			$strTableName = 'mm_' . $strTableName;
		}

		MetaModelTableManipulation::checkTablename($strTableName);

		$objMetaModel = $this->Database->prepare("SELECT tableName FROM tl_metamodel WHERE id=?")
										->limit(1)
										->executeUncached($objDC->id);

		// MetaModel not found in database or table name not changed, easy way out.
		if ($objMetaModel->numRows == 0 || $strTableName==$objMetaModel->tableName)
		{
			return $strTableName;
		}

		if (strlen($objMetaModel->tableName) && $this->Database->tableExists($objMetaModel->tableName, null, true))
		{
			MetaModelTableManipulation::renameTable($objMetaModel->tableName, $strTableName);
		} else {
			MetaModelTableManipulation::createTable($strTableName);
		}

		// TODO: notify fields that the MetaModel has changed its table name.

		return $strTableName;
	}

	public function backendSectionCallback()
	{
		return array_keys($GLOBALS['BE_MOD']);
	}

	public function getTables()
	{
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

?>