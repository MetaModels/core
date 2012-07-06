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
 * This is the IMetaModelFilterSettings reference implementation.
 *
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelFilterSettings implements IMetaModelFilterSettings
{
	protected $arrData = array();

	/**
	 * @var IMetaModelFilterSetting[]
	 */
	protected $arrSettings = array();

	public function __construct($arrData)
	{
		$this->arrData = $arrData;
	}

	protected function newSetting(Database_Result $objSettings)
	{
		$strClass = $GLOBALS['METAMODELS']['filters'][$objSettings->type]['class'];
		// TODO: add factory support here.
		if ($strClass)
		{
			return new $strClass($this, $objSettings->row());
		}
		return null;
	}

	protected function collectRulesFor($objBaseSettings, $objSetting)
	{
		$objDB = Database::getInstance();
		$objSettings = $objDB->prepare('SELECT * FROM tl_metamodel_filtersetting WHERE pid=?')->execute($objBaseSettings->id);
		while ($objSettings->next())
		{
			$objNewSetting = $this->newSetting($objSettings);
			if ($objNewSetting)
			{
				$objSetting->addChild($objNewSetting);
				// collect next level.
				if ($GLOBALS['METAMODELS']['filters'][$objNewSetting->type]['nestingAllowed'])
				{
					$this->collectRulesFor($objSettings, $objNewSetting);
				}
			}
		}
	}

	///////////////////////////////////////////////////////////////////////////////
	// IMetaModelFilterSettings
	///////////////////////////////////////////////////////////////////////////////

	public function getMetaModel()
	{
		return MetaModelFactory::byId($this->arrData['pid']);
	}

	public function collectRules()
	{
		if (!$this->arrData['id'])
		{
			throw new Exception('Error: dynamically created FilterSettings can not collect attribute information', 1);
			
		}
		$objDB = Database::getInstance();
		$objSettings = $objDB->prepare('SELECT * FROM tl_metamodel_filtersetting WHERE fid=? AND pid=0')->execute($this->arrData['id']);
		while ($objSettings->next())
		{
			$objNewSetting = $this->newSetting($objSettings);
			if ($objNewSetting)
			{
				$this->arrSettings[] = $objNewSetting;
				if ($GLOBALS['METAMODELS']['filters'][$objSettings->type]['nestingAllowed'])
				{
					$this->collectRulesFor($objSettings, $objNewSetting);
				}
			}
		}
	}

	public function addRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		foreach ($this->arrSettings as $objSetting)
		{
			$objSetting->prepareRules($objFilter, $arrFilterUrl);
		}
	}

	public function generateFilterUrlFrom(IMetaModelItem $objItem)
	{
		$arrFilterUrl = array();
		foreach ($this->arrSettings as $objSetting)
		{
			$arrFilterUrl = array_merge($arrFilterUrl, $objSetting->generateFilterUrlFrom($objItem));
		}
		return $arrFilterUrl;
	}
}

?>