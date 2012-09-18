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
 * This class is used from DCA tl_metamodel_dca for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class TableMetaModelDca extends Backend
{
	/**
	 * Render a row for the list view in the backend.
	 *
	 * @param array         $arrRow   the current data row.
	 * @param string        $strLabel the label text.
	 * @param DataContainer $objDC    the DataContainer instance that called the method.
	 */
	public function getRowLabel($arrRow)
	{
		return 'HULLA';

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

		return '<span class="name">'.$strLabel. '</span>'.$strImage;
	}

	public function getTables()
	{
		$arrTables = array();
		foreach($GLOBALS['BE_MOD'] as $strGroup => $arrGroup)
		{
			foreach($arrGroup as $strModule => $arrModule)
			{
				if ($strModule == 'metamodels')
				{
					continue;
				}
				if ($arrModule['tables'])
				{
					foreach ($arrModule['tables'] as $strTable)
					{
						$strAdd = $strTable;
						if (substr($strTable, 0, 2) == 'mm')
						{
							$objMetaModel = MetaModelFactory::byTableName($strTable);

							if ($objMetaModel)
							{
								$strAdd = sprintf('%s (%s)', $strTable, $objMetaModel->get('name'));
							}
						}
						$arrTables[$strModule][$strTable] = $strAdd;
					}
				}
			}
		}
		return $arrTables;
	}
}

?>