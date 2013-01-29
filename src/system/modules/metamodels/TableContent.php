<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}


/**
 * Provides be-functionalities
 *
 * @package	   MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 */
class TableContent extends Backend
{
	public function buildCustomFilter($objDC)
	{
		// Check if we have a id, no create mode
		if (is_null($objDC->id))
		{
			unset($GLOBALS['TL_DCA']['tl_content']['fields']['metamodel_filterparams']);
			return;
		}

		// Get basic informations
		$objContent = $this->Database
				->prepare('SELECT type, metamodel, metamodel_filtering FROM tl_content WHERE id=?')
				->limit(1)
				->execute($objDC->id);

		$intMetaModel = $objContent->metamodel;
		$intFilter = $objContent->metamodel_filtering;

		// Check if we have a row/metaModelconten/MetaModel/Filter
		if ($objContent->numRows == 0 || $objContent->type != 'metamodel_content' || empty($intMetaModel) || empty($intFilter))
		{
			unset($GLOBALS['TL_DCA']['tl_content']['fields']['metamodel_filterparams']);
			return;
		}

		$objFilter = $objFilterSettings = MetaModelFilterSettingsFactory::byId($intFilter);
		$arrParams = $objFilter->getParameterDCA();
		$GLOBALS['TL_DCA']['tl_content']['fields']['metamodel_filterparams']['eval']['subfields'] = $arrParams;
	}

	/**
	 * Fetch the template group for the current MetaModel module.
	 *
	 * @param DataContainer $objDC the datacontainer calling this method.
	 *
	 * @return array
	 *
	 */
	public function getModuleTemplates(DataContainer $objDC)
	{
		return $this->getTemplateGroup('ce_metamodel_', $objDC->activeRecord->pid);
	}

	/**
	 * get frontend templates for filters
	 * @param DataContainer
	 * @return array
	 */
	public function getFilterTemplates(DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;

		if ($this->Input->get('act') == 'overrideAll')
		{
			$intPid = $this->Input->get('id');
		}

		return $this->getTemplateGroup('mm_filter_', $intPid);
	}

	/**
	 * get filters
	 * @param object
	 * @return array
	 */
	public function getFilter($objRow)
	{
		$return = array();

		$objFilter = $this->Database->prepare("SELECT id,name FROM tl_metamodel_filter WHERE pid=? ORDER BY name")
			->execute($objRow->activeRecord->metamodel);

		while($objFilter->next())
		{
			$return[$objFilter->id] = $objFilter->name;
		}

		return $return;
	}

	/**
	 * Fetch all attribute names for the current metamodel
	 *
	 * @param DataContainer $objDC the datacontainer calling this method.
	 *
	 * @return string[string] array of all attributes as colName => human name
	 */
	public function getAttributeNames(DataContainer $objDC)
	{
		$arrAttributeNames = array('sorting' => $GLOBALS['TL_LANG']['MSC']['sorting']);
		$objMetaModel = MetaModelFactory::byId($objDC->activeRecord->metamodel);
		if ($objMetaModel)
		{
			foreach ($objMetaModel->getAttributes() as $objAttribute)
				$arrAttributeNames[$objAttribute->getColName()] = $objAttribute->getName();
		}

		return $arrAttributeNames;
	}

	/**
	 * get attributes for checkbox wizard
	 * @param object
	 * @return array
	 */
	public function getFilterParameterNames($objRow)
	{
		$return = array();

		if(!$objRow->activeRecord->metamodel_filtering)
		{
			return $return;
		}

		$objFilterSetting = MetaModelFilterSettingsFactory::byId($objRow->activeRecord->metamodel_filtering);
		$arrParameterDca = $objFilterSetting->getParameterFilterNames();

		return $arrParameterDca;
	}

	/**
	 * Return the edit wizard
	 * @param DataContainer $dc the datacontainer
	 * @return string
	 */
	public function editMetaModel(DataContainer $dc)
	{
		return ($dc->value < 1) ? '' : sprintf('<a href="contao/main.php?%s&amp;act=edit&amp;id=%s" title="%s" style="padding-left:3px">%s</a>',
			'do=metamodels',
			$dc->value,
			sprintf(specialchars($GLOBALS['TL_LANG']['tl_module']['editmetamodel'][1]), $dc->value),
			$this->generateImage('alias.gif', $GLOBALS['TL_LANG']['tl_module']['editmetamodel'][0], 'style="vertical-align:top"')
		);
	}

	/**
	 * Return the edit wizard
	 * @param DataContainer $dc the datacontainer
	 * @return string
	 */
	public function editFilterSetting(DataContainer $dc)
	{
		return ($dc->value < 1) ? '' : sprintf('<a href="contao/main.php?%s&amp;id=%s" title="%s" style="padding-left:3px">%s</a>',
			'do=metamodels&table=tl_metamodel_filtersetting',
			$dc->value,
			sprintf(specialchars($GLOBALS['TL_LANG']['tl_module']['editfiltersetting'][1]), $dc->value),
			$this->generateImage('alias.gif', $GLOBALS['TL_LANG']['tl_module']['editfiltersetting'][0], 'style="vertical-align:top"')
		);
	}

	/**
	 * Return the edit wizard
	 * @param DataContainer $dc the datacontainer
	 * @return string
	 */
	public function editRenderSetting(DataContainer $dc)
	{
		return ($dc->value < 1) ? '' : sprintf('<a href="contao/main.php?%s&amp;act=edit&amp;id=%s" title="%s" style="padding-left:3px">%s</a>', 'do=metamodels&table=tl_metamodel_rendersettings', $dc->value, sprintf(specialchars($GLOBALS['TL_LANG']['tl_module']['editrendersetting'][1]), $dc->value), $this->generateImage('alias.gif', $GLOBALS['TL_LANG']['tl_module']['editrendersetting'][0], 'style="vertical-align:top"')
				);
	}

	/**
	 * Fetch all available filter settings for the current meta model.
	 *
	 * @param DataContainer $objDC the datacontainer calling this method.
	 *
	 * @return string[int] array of all attributes as id => human name
	 */
	public function getFilterSettings(DataContainer $objDC)
	{
		$objDB = Database::getInstance();
		$objFilterSettings = $objDB->prepare('SELECT * FROM tl_metamodel_filter WHERE pid=?')->execute($objDC->activeRecord->metamodel);
		$arrSettings = array();
		while ($objFilterSettings->next())
		{
			$arrSettings[$objFilterSettings->id] = $objFilterSettings->name;
		}

		//sort the filtersettings
		asort($arrSettings);
		return $arrSettings;
	}

	/**
	 * Fetch all available render settings for the current meta model.
	 *
	 * @param DataContainer $objDC the datacontainer calling this method.
	 *
	 * @return string[int] array of all attributes as id => human name
	 */
	public function getRenderSettings($objDC)
	{
		$objDB = Database::getInstance();
		$objFilterSettings = $objDB->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE pid=?')->execute($objDC->activeRecord->metamodel);

		$arrSettings = array();
		while ($objFilterSettings->next())
		{
			$arrSettings[$objFilterSettings->id] = $objFilterSettings->name;
		}

		//sort the rendersettings
		asort($arrSettings);
		return $arrSettings;
	}
}

