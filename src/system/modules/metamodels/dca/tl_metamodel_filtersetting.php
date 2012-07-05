<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Backend
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
 * Table tl_metamodel_attribute 
 */

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting'] = array
(
	'config' => array
	(
		'dataContainer'               => 'Table',
		'switchToEdit'                => false,
		'enableVersioning'            => false,
		'oncreate_callback'           => array(array('TableMetaModelFilterSetting', 'create_callback'))
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 5,
			'fields'                  => array('attr_id'),
			'panelLayout'             => 'filter,limit', 
			'headerFields'            => array('type', 'attr_id'), 
			'flag'                    => 1,
			'icon'                    => 'system/modules/metamodels/html/filter_and.png',
			'paste_button_callback'   => array('TableMetaModelFilterSetting', 'pasteButton'),
		),

		'label' => array
		(
			'fields'                  => array('type'),
			'format'                  => '%s',
			'label_callback'          => array('TableMetaModelFilterSetting', 'drawSetting')
		),

		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),

		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
		)
	),

	'palettes' => array
	(
		'__selector__' => array('type', 'attr_id')
	),

	'metapalettes' => array
	(
		'default' => array
		(
			'title' => array('type')
		),
		'_attribute_ extends default' => array
		(
			'+title' => array('attr_id after type')
		),

		// base rules shipped with metamodels.

		'simplelookup extends _attribute_' => array
		(
			'config' => array('urlparam'),
		),

	),

	// Fields
	'fields' => array
	(
		'fid' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['fid']
			// keep this empty but keep it here!
			// needed for act=copy in DC_Table, as otherwise the fid value will not be copied.
		),

		'type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['type'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModelFilterSetting', 'getSettingTypes'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames'],
			'eval'                    => array(
				'doNotSaveEmpty'      => true,
				'alwaysSave'          => true,
				'submitOnChange'      => true,
				'includeBlankOption'  => true,
				'mandatory'           => true,
				'tl_class'            =>'w50'
			),
		),

		'attr_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['attr_id'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModelFilterSetting', 'getAttributeNames'),
			'eval'                    => array(
				'doNotSaveEmpty'      => true,
				'alwaysSave'          => true,
				'submitOnChange'      => true,
				'includeBlankOption'  => true,
				'mandatory'           => true,
				'tl_class'            =>'w50'
			),
			'load_callback'         => array(array('TableMetaModelFilterSetting', 'attrIdToName')),
			'save_callback'         => array(array('TableMetaModelFilterSetting', 'nameToAttrId')),
		),

		'urlparam' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['urlparam'],
			'exclude'                 => true,
			'inputType'               => 'text',
		),

	)
);

?>