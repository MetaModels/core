<?php

if (!defined('TL_ROOT'))
	die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Leo Feyer 2005-2012
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Backend
 * @license    LGPL
 * @filesource
 */
/**
 * Config
 */
$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('tl_content_metamodel', 'buildCustomFilter');

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['metamodel_content'] = '{title_legend},name,headline,type;{mm_config_legend},metamodel,perPage,metamodel_use_limit;{mm_filter_legend},metamodel_sortby,metamodel_sortby_direction,metamodel_filtering,metamodel_filterparams;{mm_rendering},metamodel_layout,metamodel_rendersettings,metamodel_noparsing;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'metamodel_use_limit';

// Insert new Subpalettes after position 1
array_insert($GLOBALS['TL_DCA']['tl_content']['subpalettes'], 1, array(
	'metamodel_use_limit' => 'metamodel_offset,metamodel_limit',
));

/**
 * Fields
 */
array_insert($GLOBALS['TL_DCA']['tl_content']['fields'], 1, array(
	'metamodel' => array
		(
		'label' => &$GLOBALS['TL_LANG']['tl_content']['metamodel'],
		'exclude' => true,
		'inputType' => 'select',
		'foreignKey' => 'tl_metamodel.name',
		'eval' => array
			(
			'mandatory' => true,
			'submitOnChange' => true
		),
		'wizard' => array
			(
			array('tl_content_metamodel', 'editMetaModel')
		)
	),
	'metamodel_layout' => array
		(
		'label' => &$GLOBALS['TL_LANG']['tl_content']['metamodel_layout'],
		'exclude' => true,
		'inputType' => 'select',
		'options_callback' => array('tl_content_metamodel', 'getModuleTemplates'),
		'eval' => array('tl_class' => 'w50')
	),
	'metamodel_use_limit' => array
		(
		'label' => &$GLOBALS['TL_LANG']['tl_content']['metamodel_use_limit'],
		'exclude' => true,
		'inputType' => 'checkbox',
		'eval' => array('submitOnChange' => true, 'tl_class' => 'w50 m12'),
	),
	'metamodel_limit' => array
		(
		'label' => &$GLOBALS['TL_LANG']['tl_content']['metamodel_limit'],
		'exclude' => true,
		'inputType' => 'text',
		'eval' => array('rgxp' => 'digit', 'tl_class' => 'w50')
	),
	'metamodel_offset' => array
		(
		'label' => &$GLOBALS['TL_LANG']['tl_content']['metamodel_offset'],
		'exclude' => true,
		'inputType' => 'text',
		'eval' => array('rgxp' => 'digit', 'tl_class' => 'w50'),
	),
	'metamodel_sortby' => array
		(
		'label' => &$GLOBALS['TL_LANG']['tl_content']['metamodel_sortby'],
		'exclude' => true,
		'inputType' => 'select',
		'options_callback' => array('tl_content_metamodel', 'getAttributeNames'),
		'eval' => array('includeBlankOption' => true, 'tl_class' => 'w50'),
	),
	'metamodel_sortby_direction' => array
		(
		'label' => &$GLOBALS['TL_LANG']['tl_content']['metamodel_sortby_direction'],
		'exclude' => true,
		'inputType' => 'select',
		'reference' => &$GLOBALS['TL_LANG']['tl_content'],
		'options' => array('ASC' => 'ASC', 'DESC' => 'DESC'),
		'eval' => array('includeBlankOption' => false, 'tl_class' => 'w50'),
	),
	'metamodel_filtering' => array
		(
		'label' => &$GLOBALS['TL_LANG']['tl_content']['metamodel_filtering'],
		'exclude' => true,
		'inputType' => 'select',
		'options_callback' => array('tl_content_metamodel', 'getFilterSettings'),
		'default' => '',
		'eval' => array
			(
			'includeBlankOption' => true,
			'submitOnChange' => true,
			'tl_class' => 'clr'
		),
		'wizard' => array
			(
			array('tl_content_metamodel', 'editFilterSetting')
		)
	),
	'metamodel_rendersettings' => array
		(
		'label' => &$GLOBALS['TL_LANG']['tl_content']['metamodel_rendersettings'],
		'exclude' => true,
		'inputType' => 'select',
		'options_callback' => array('tl_content_metamodel', 'getRenderSettings'),
		'default' => '',
		'eval' => array
			(
			'includeBlankOption' => true,
			'submitOnChange' => true,
			'tl_class' => 'w50'
		),
		'wizard' => array
			(
			array('tl_content_metamodel', 'editRenderSetting')
		)
	),
	'metamodel_noparsing' => array
		(
		'label' => &$GLOBALS['TL_LANG']['tl_content']['metamodel_noparsing'],
		'exclude' => true,
		'inputType' => 'checkbox',
		'eval' => array
			(
			'submitOnChange' => true,
			'tl_class' => 'w50'
		),
	),
	'metamodel_filterparams' => array
		(
		'label' => &$GLOBALS['TL_LANG']['tl_content']['metamodel_filterparams'],
		'exclude' => true,
		'inputType' => 'mm_subdca',
		'eval' => array
		(
			'tl_class' => 'clr m12',
			'flagfields' => array
			(
				'use_get' => array
				(
					'label' => &$GLOBALS['TL_LANG']['tl_content']['metamodel_filterparams_use_get'],
					'inputType' => 'checkbox'
				),
			),
		)
	)
));

/**
 * complementary methods needed by the DCA.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
class tl_content_metamodel extends Backend
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
	 * Return the edit wizard
	 * @param DataContainer $dc the datacontainer
	 * @return string
	 */
	public function editMetaModel(DataContainer $dc)
	{
		return ($dc->value < 1) ? '' : sprintf('<a href="contao/main.php?%s&amp;act=edit&amp;id=%s" title="%s" style="padding-left:3px">%s</a>', 'do=metamodels', $dc->value, sprintf(specialchars($GLOBALS['TL_LANG']['tl_module']['editmetamodel'][1]), $dc->value), $this->generateImage('alias.gif', $GLOBALS['TL_LANG']['tl_module']['editmetamodel'][0], 'style="vertical-align:top"')
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
	 * Return the edit wizard
	 * @param DataContainer $dc the datacontainer
	 * @return string
	 */
	public function editFilterSetting(DataContainer $dc)
	{

		$strReturn = '';

		if (($dc->value < 1))
		{
			return $strReturn;
		}

		$strReturn .= sprintf('<a href="contao/main.php?%s&amp;act=edit&amp;id=%s" title="%s" style="padding-left:3px">%s</a>', 'do=metamodels&table=tl_metamodel_filter', $dc->value, sprintf(specialchars($GLOBALS['TL_LANG']['tl_module']['editfiltersetting'][1]), $dc->value), $this->generateImage('alias.gif', $GLOBALS['TL_LANG']['tl_module']['editfiltersetting'][0], 'style="vertical-align:top"')
		);
		return $strReturn;
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

?>