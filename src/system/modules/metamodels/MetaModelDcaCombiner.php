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
class MetaModelDcaCombiner extends Backend
{
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}

	protected function getGroups($strTable='user')
	{
		// TODO: we need some way to limit the user groups from somewhere else, like the model itself or something like that.
		if (!in_array($strTable, array('user', 'member')))
		{
			throw new Exception('unkown table name ' . $strTable, 1);
		}
		$objGroups = $this->Database->execute(sprintf('SELECT id,name FROM tl_%s_group', $strTable));

		$arrReturn = array();
		if($strTable == 'user')
		{
			$arrReturn[-1] = $GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['sysadmin'];
		}

		while ($objGroups->next())
		{
			$arrReturn[$objGroups->id] = $objGroups->name;
		}
		return $arrReturn;
	}

	protected function getPalettes($intMetaModel)
	{
		$objPalettes = $this->Database->prepare('SELECT id,name FROM tl_metamodel_dca WHERE pid=?')->execute($intMetaModel);

		$arrReturn = array();
		while ($objPalettes->next())
		{
			$arrReturn[$objPalettes->id] = $objPalettes->name;
		}
		return $arrReturn;
	}

	protected function getViews($intMetaModel)
	{
		$objViews = $this->Database->prepare('SELECT id,name FROM tl_metamodel_rendersettings WHERE pid=?')->execute($intMetaModel);

		$arrReturn = array();
		while ($objViews->next())
		{
			$arrReturn[$objViews->id] = $objViews->name;
		}
		return $arrReturn;
	}

	public function getMemberGroups()
	{
		return $this->getGroups('member');
	}

	public function getUserGroups()
	{
		return $this->getGroups('user');
	}

	public function getModelPalettes(/*$objDC <- would be nice but we are called from a MCW and therefore that is passed in here instead of the DC. :/ */)
	{
		return $this->getPalettes($this->Input->get('id')/*$objDC->getId()*/);
	}

	public function getModelViews(/*$objDC <- would be nice but we are called from a MCW and therefore that is passed in here instead of the DC. :/ */)
	{
		return $this->getViews($this->Input->get('id')/*$objDC->getId()*/);
	}

	public function updateSort($arrData/*, $objDC*/)
	{
		foreach($arrData as $i => &$arrRow)
		{
			$arrData[$i]['sorting'] = ($i+1)*128;
			$arrData[$i]['tstamp'] = time();
		}
		return $arrData;
	}
}

?>