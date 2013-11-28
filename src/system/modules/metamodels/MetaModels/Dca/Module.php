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

namespace MetaModels\Dca;

use MetaModels\Factory as ModelFactory;
use MetaModels\Filter\Setting\Factory as FilterFactory;

/**
 * complementary methods needed by the DCA.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */
class Module extends \Backend
{

	public function buildFilterParams($objDC)
	{
		// Get basic informations
		$objModule = \Database::getInstance()->prepare(
			'SELECT	m.metamodel_filtering
			FROM	tl_module AS m
			JOIN	tl_metamodel AS mm ON mm.id = m.metamodel
			WHERE	m.id = ?
			AND		m.type = ?'
		)->limit(1)->execute($objDC->id, 'metamodel_list');

		if(!$objModule->metamodel_filtering) {
			unset($GLOBALS['TL_DCA']['tl_module']['fields']['metamodel_filterparams']);
			return;
		}

		$objFilterSettings = FilterFactory::byId($objModule->metamodel_filtering);
		$GLOBALS['TL_DCA']['tl_module']['fields']['metamodel_filterparams']['eval']['subfields'] = $objFilterSettings->getParameterDCA();
	}

	/**
	 * Fetch the template group for the current MetaModel module.
	 *
	 * @param \DataContainer $objDC the datacontainer calling this method.
	 *
	 * @return array
	 *
	 */
	public function getModuleTemplates(\DataContainer $objDC)
	{
		return $this->getTemplateGroup('mod_' . $objDC->activeRecord->type, $objDC->activeRecord->pid);
	}

	/**
	 * get frontend templates for filters
	 *
	 * @param \DataContainer
	 *
	 * @return array
	 */
	public function getFilterTemplates(\DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;

		if ($this->Input->get('act') == 'overrideAll')
		{
			$intPid = $this->Input->get('id');
		}

		return $this->getTemplateGroup('mm_filter_', $intPid);
	}

	/**
	 * Fetch all attribute names for the current metamodel
	 *
	 * @param \DataContainer $objDC the datacontainer calling this method.
	 *
	 * @return string[string] array of all attributes as colName => human name
	 */
	public function getAttributeNames(\DataContainer $objDC)
	{
		$arrAttributeNames = array('sorting' => $GLOBALS['TL_LANG']['MSC']['sorting']);
		$objMetaModel = ModelFactory::byId($objDC->activeRecord->metamodel);
		if ($objMetaModel)
		{
			foreach ($objMetaModel->getAttributes() as $objAttribute)
				$arrAttributeNames[$objAttribute->getColName()] = $objAttribute->getName();
		}

		return $arrAttributeNames;
	}

	/**
	 * Return the edit wizard
	 * @param \DataContainer $dc the datacontainer
	 * @return string
	 */
	public function editMetaModel(\DataContainer $dc)
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
	 * @param \DataContainer $dc the datacontainer
	 * @return string
	 */
	public function editRenderSetting(\DataContainer $dc)
	{
		return ($dc->value < 1) ? '' : sprintf('<a href="contao/main.php?%s&amp;id=%s" title="%s" style="padding-left:3px">%s</a>',
			'do=metamodels&table=tl_metamodel_rendersetting',
			$dc->value,
			sprintf(specialchars($GLOBALS['TL_LANG']['tl_module']['editrendersetting'][1]), $dc->value),
			$this->generateImage('alias.gif', $GLOBALS['TL_LANG']['tl_module']['editrendersetting'][0], 'style="vertical-align:top"')
		);
	}

	/**
	 * Return the edit wizard
	 * @param \DataContainer $dc the datacontainer
	 * @return string
	 */
	public function editFilterSetting(\DataContainer $dc)
	{
		return ($dc->value < 1) ? '' : sprintf('<a href="contao/main.php?%s&amp;id=%s" title="%s" style="padding-left:3px">%s</a>',
			'do=metamodels&table=tl_metamodel_filtersetting',
			$dc->value,
			sprintf(specialchars($GLOBALS['TL_LANG']['tl_module']['editfiltersetting'][1]), $dc->value),
			$this->generateImage('alias.gif', $GLOBALS['TL_LANG']['tl_module']['editfiltersetting'][0], 'style="vertical-align:top"')
		);
	}

	/**
	 * Fetch all available filter settings for the current meta model.
	 *
	 * @param \DataContainer $objDC the datacontainer calling this method.
	 *
	 * @return string[int] array of all attributes as id => human name
	 */
	public function getFilterSettings(\DataContainer $objDC)
	{
		$objDB = \Database::getInstance();
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
	 * Fetch all available filter settings for the current meta model.
	 *
	 * @param \DataContainer $objDC the datacontainer calling this method.
	 *
	 * @return string[int] array of all attributes as id => human name
	 */
	public function getRenderSettings(\DataContainer $objDC)
	{
		$objDB = \Database::getInstance();
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

		$objFilterSetting = FilterFactory::byId($objRow->activeRecord->metamodel_filtering);
		$arrParameterDca = $objFilterSetting->getParameterFilterNames();

		return $arrParameterDca;
	}

	/**
	 * Get a list with all allowed attributes for meta title.
	 * 
	 * @param DataContainer $objDC
	 * 
	 * @return array A list with all found attributes.
	 */
	public function getMetaTitleAttributes(DataContainer $objDC)
	{
		$arrAttributeNames = array();

		$objMetaModel = MetaModelFactory::byId($objDC->activeRecord->metamodel);
		if ($objMetaModel)
		{
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				if (in_array($objAttribute->get('type'), $GLOBALS['METAMODELS']['metainformation']['allowedTitle']))
				{
					$arrAttributeNames[$objAttribute->getColName()] = $objAttribute->getName() . ' [' . $objAttribute->getColName() . ']</span>';
				}
			}
		}

		return $arrAttributeNames;
	}

	/**
	 * Get a list with all allowed attributes for meta description.
	 * 
	 * @param DataContainer $objDC
	 * 
	 * @return array A list with all found attributes.
	 */
	public function getMetaDescriptionAttributes(DataContainer $objDC)
	{
		$arrAttributeNames = array();

		$objMetaModel = MetaModelFactory::byId($objDC->activeRecord->metamodel);
		if ($objMetaModel)
		{
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				if (in_array($objAttribute->get('type'), $GLOBALS['METAMODELS']['metainformation']['allowedDescription']))
				{
					$arrAttributeNames[$objAttribute->getColName()] = $objAttribute->getName() . ' [' . $objAttribute->getColName() . ']</span>';
				}
			}
		}

		return $arrAttributeNames;
	}
}

