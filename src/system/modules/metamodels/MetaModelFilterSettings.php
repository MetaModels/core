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

	/**
	 * @param Database_Result $objSettings The information from which to initialize the setting from
	 *
	 * @return IMetaModelFilterSetting
	 */
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

	/**
	 * Fetch all child rules for the given setting.
	 *
	 * @param Database_Result                   $objBaseSettings The database information of the parent setting.
	 *
	 * @param IMetaModelFilterSettingWithChilds $objSetting      The information from which to initialize the setting from.
	 *
	 * @return void
	 */
	protected function collectRulesFor($objBaseSettings, $objSetting)
	{
		$objDB = Database::getInstance();
		$objSettings = $objDB->prepare('SELECT * FROM tl_metamodel_filtersetting WHERE pid=? AND enabled=1 ORDER BY sorting ASC')->execute($objBaseSettings->id);

		while ($objSettings->next())
		{
			$objNewSetting = $this->newSetting($objSettings);
			if ($objNewSetting)
			{
				$objSetting->addChild($objNewSetting);
				// collect next level.
				if ($GLOBALS['METAMODELS']['filters'][$objNewSetting->get('type')]['nestingAllowed'])
				{
					$this->collectRulesFor($objSettings, $objNewSetting);
				}
			}
		}
	}

	///////////////////////////////////////////////////////////////////////////////
	// IMetaModelFilterSettings
	///////////////////////////////////////////////////////////////////////////////

	/**
	 * retrieve the MetaModel this filter belongs to.
	 *
	 * @return IMetaModel
	 */
	public function getMetaModel()
	{
		if (!$this->arrData['pid'])
		{
			throw new Exception(sprintf('Error: Filtersetting %d not attached to a MetaModel', $this->arrData['id']));

		}
		return MetaModelFactory::byId($this->arrData['pid']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function collectRules()
	{
		if (!$this->arrData['id'])
		{
			throw new Exception('Error: dynamically created FilterSettings can not collect attribute information', 1);
		}

		$objDB = Database::getInstance();
		$objSettings = $objDB->prepare('SELECT * FROM tl_metamodel_filtersetting WHERE fid=? AND pid=0 AND enabled=1 ORDER BY sorting ASC')->execute($this->arrData['id']);
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

	/**
	 * {@inheritdoc}
	 */
	public function addRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		foreach ($this->arrSettings as $objSetting)
		{
			$objSetting->prepareRules($objFilter, $arrFilterUrl);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function generateFilterUrlFrom(IMetaModelItem $objItem, IMetaModelRenderSettings $objRenderSetting)
	{
		$arrFilterUrl = array();
		foreach ($this->arrSettings as $objSetting)
		{
			$arrFilterUrl = array_merge($arrFilterUrl, $objSetting->generateFilterUrlFrom($objItem, $objRenderSetting));
		}
		return $arrFilterUrl;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameters()
	{
		$arrParams = array();
		foreach ($this->arrSettings as $objSetting)
		{
			$arrParams = array_merge($arrParams, $objSetting->getParameters());
		}
		return $arrParams;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterDCA()
	{
		$arrParams = array();
		foreach ($this->arrSettings as $objSetting)
		{
			$arrParams = array_merge($arrParams, $objSetting->getParameterDCA());
		}
		return $arrParams;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterFilterNames()
	{
		$arrParams = array();

		foreach ($this->arrSettings as $objSetting)
		{
			$arrParams = array_merge($arrParams, $objSetting->getParameterFilterNames());
		}
		return $arrParams;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterFilterWidgets($arrFilterUrl, $arrJumpTo = array(), MetaModelFrontendFilterOptions $objFrontendFilterOptions)
	{
		$arrParams = array();

		$objFilter = $this->getMetaModel()->getEmptyFilter();
		$this->addRules($objFilter, $arrFilterUrl);

		$arrIds = $objFilter->getMatchingIds();
		
		foreach ($this->arrSettings as $objSetting)
		{
			$arrParams = array_merge($arrParams, $objSetting->getParameterFilterWidgets($arrIds, $arrFilterUrl, $arrJumpTo, $objFrontendFilterOptions));
		}

		return $arrParams;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getReferencedAttributes()
	{
		$arrAttributes = array();

		foreach ($this->arrSettings as $objSetting)
		{
			$arrAttributes = array_merge($arrAttributes, $objSetting->getReferencedAttributes());
		}

		return $arrAttributes;
	}

}

