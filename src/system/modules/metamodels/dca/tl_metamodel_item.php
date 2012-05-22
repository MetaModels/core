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

$this->loadLanguageFile('tl_metamodel');

/**
 * This file defines the basic structure of ALL MetaModels.
 * Note however, that various MetaModel extensions might remove or add stuff here.
 */

/**
 * Table tl_metamodel_item 
 */
$GLOBALS['TL_DCA']['tl_metamodel_item'] = array
(
	// DC_MetaModel container config
	'config' => array
	(
		'dataContainer'               => 'MetaModel',
		'switchToEdit'                => false,
		'enableVersioning'            => false,
	),
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1, // 1 default sorting value, 2 switchable sorting value
			// TODO: panelLayout must be built dynamically in getMetaModelDca() to solve issue #199
			'headerFields'            => array('tstamp'),
			'fields'                  => array('sorting'),
			'child_record_callback'   => array('MetaModelDatabase', 'renderRow'),
		),
		
		'label' => array
		(
			'fields'                  => array('tstamp'),
			'format'                  => '%s',
			'label_callback'          => array('MetaModelDatabase', 'labelCallback'),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_item']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_item']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_item']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_item']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_item']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		),
	),
	'palettes' => array
	(
	),
	'subpalettes' => array
	(
	),
	'fields' => array
	(
	)
);

?>