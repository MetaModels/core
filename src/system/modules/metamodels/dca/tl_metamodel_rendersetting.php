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

$GLOBALS['TL_DCA']['tl_metamodel_rendersetting'] = array
(
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_metamodel_rendersettings',
		'switchToEdit'                => true,
		'enableVersioning'            => false,
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'limit',
			'headerFields'            => array('name'),
			'child_record_callback'   => array('TableMetaModelRenderSetting', 'drawSetting'),
		),

		'global_operations' => array
		(
			'addall' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addall'],
				'href'                => 'key=rendersetting_addall',
				'class'               => 'rendersetting_addall',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
		)
	),

	'palettes' => array
	(
		'__selector__' => array('attr_id')
	),

	'metapalettes' => array
	(
		'default' => array
		(
			'title' => array('attr_id', 'template')
		),
	),

	// Fields
	'fields' => array
	(
		'attr_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['attr_id'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModelRenderSetting', 'getAttributeNames'),
			'eval'                    => array(
				'doNotSaveEmpty'      => true,
				'alwaysSave'          => true,
				'submitOnChange'      => true,
				'includeBlankOption'  => true,
				'mandatory'           => true,
			),
		),

		'template' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['template'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModelRenderSetting', 'getTemplates'),
			'eval'                    => array
			(
				'tl_class'=>'w50',
				'includeBlankOption'  => true,
			)
		),
	)
);

?>