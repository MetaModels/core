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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

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
		'dataContainer'               => 'General',
		'switchToEdit'                => false,
		'enableVersioning'            => false,
	),
	'dca_config' => array
	(
		'callback' => 'GeneralCallbackMetaModel',
		'data_provider' => array
		(
			'default' => array
			(
				'class'  => 'GeneralDataMetaModel',
				'source' => null // get's set within MetaModelDatabase::createDataContainer via $GLOBALS['TL_HOOKS']['loadDataContainer']
			)
		),
		'controller' => 'GeneralControllerMetaModel',
		'view' => 'GeneralViewMetaModel'
	),

	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1, // 1 default sorting value, 2 switchable sorting value
			'panelLayout'             => 'limit',
			'headerFields'            => array('tstamp'),
			'paste_button_callback'   => array('MetaModelDatabase', 'pasteButton'),
		),

		'label' => array
		(
			'fields'                  => array(),
			'format'                  => 'unused',
//			'label_callback'          => array('MetaModelDatabase', 'labelCallback'),
		),

        // not yet implemented
        /* 'global_operations' => array
        (
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset();"'
            )
        ), */

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
				'icon'                => 'copy.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
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
		'sorting' => array
		(
			'sorting' => true
		)
	)
);

