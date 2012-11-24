<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package       MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Table tl_metamodel_dca
 */

$GLOBALS['TL_DCA']['tl_metamodel_dca'] = array
(

	// Config
	'config' => array
	(
		'dataContainer' => 'General',
		'ptable' => 'tl_metamodel',
		'ctable' => 'tl_metamodel_dcasetting',
		'switchToEdit' => false,
		'enableVersioning' => false,
//		'oncreate_callback'                => array(array('TableMetaModelDca', 'checkSortMode')),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('name'),
			'panelLayout'             => 'filter,limit',
			'headerFields'            => array('name'),
			'flag'                    => 1,
		),

		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s',
			'label_callback'          => array('TableMetaModelDca', 'drawSetting')
		),

		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
		),

		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'settings' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['settings'],
				'href'                => 'table=tl_metamodel_dcasetting',
				'icon'                => 'system/modules/metamodels/html/palettesetting.png',
			),
		)
	),

	// Palettes
	'metapalettes' => array
	(
		'default' => array
		(
			'title' => array
			(
				'name',
				'isdefault'
			),
			'view' => array
			(
				'panelLayout',
			),
			'backend' => array
			(
				'rendertype',
			),
		)
	),

	'metasubselectpalettes' => array
	(
		'rendertype' => array
		(
			'standalone' => array
			(
				'mode',
				'backendsection',
				'backendicon',
				'backendcaption'
			),
			'ctable' => array
			(
				'ptable',
				'mode',
				'backendicon',
				'backendcaption'
			)
		),

		'mode' => array
		(
			/*
				0 Records are not sorted
				1 Records are sorted by a fixed field
				2 Records are sorted by a switchable field
				3 Records are sorted by the parent table
				4 Displays the child records of a parent record (see style sheets module)
				5 Records are displayed as tree (see site structure)
				6 Displays the child records within a tree structure (see articles module)
			*/
			'mode_0'  => array('flag'),
			'mode_1'  => array('flag'),
			'mode_2'  => array('flag'),
			'mode_3'  => array(''),
			'mode_4'  => array(''), // TODO: select parent head fields here.
			'mode_5'  => array(''),
			'mode_6'  => array('ptable'),
		),
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>64, 'tl_class'=>'w50')
		),

		'isdefault' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['isdefault'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50 m12 cbx')
		),

		'rendertype' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertype'],
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModelDca', 'getRenderTypes'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertypes'],
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'submitOnChange'      => true,
				'includeBlankOption'  => true
			)
		),

		'ptable' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['ptable'],
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModelDca', 'getTables'),
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'submitOnChange'      => true,
				'includeBlankOption'  => true
			)
		),

		'mode' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['mode'],
			'inputType'               => 'select',
			'default'                 => '',
			'options_callback'        => array('TableMetaModelDca', 'getValidModes'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode'],
			/*
				0 Records are not sorted
				1 Records are sorted by a fixed field
				2 Records are sorted by a switchable field
				3 Records are sorted by the parent table
				4 Displays the child records of a parent record (see style sheets module)
				5 Records are displayed as tree (see site structure)
				6 Displays the child records within a tree structure (see articles module)
			*/
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'submitOnChange'      => true
			),
			'load_callback'           => array
			(
				array('TableMetaModelDca', 'modeLoad')
			),
			'save_callback'           => array
			(
				array('TableMetaModelDca', 'modeSave')
			)
		),

		'flag' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['flag'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'),
			'eval'                    => array('tl_class'=>'w50'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']
		),

		'backendsection' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendsection'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'reference'               => &$GLOBALS['TL_LANG']['MOD'],
			'eval'                    => array
			(
				'includeBlankOption'  => true,
				'valign'              => 'top',
				'chosen'              => true,
				'tl_class'            => 'w50'
			),
			'options_callback'        => array('TableMetaModelDca', 'backendSectionCallback'),
		),

		'backendicon' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendicon'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array
			(
				'fieldType'           => 'radio',
				'files'               => true,
				'filesOnly'           => true,
				'extensions'          => 'jpg,jpeg,gif,png,tif,tiff',
				'tl_class'            => 'clr'
			)
		),

		'backendcaption' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendcaption'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval'             => array
			(
				'columnFields' => array
				(
					'langcode' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_langcode'],
						'exclude'               => true,
						'inputType'             => 'select',
						'options'               => $this->getLanguages(),
						'eval'                  => array
						(
							'style' => 'width:200px',
							'chosen'=> 'true'
						)
					),
					'label' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_label'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style' => 'width:180px',
						)
					),
					'description' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_description'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style' => 'width:200px',
						)
					),
				),
			)
		),
		'panelLayout' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['panelLayout'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'tl_class'            => 'clr long wizard',
			),
			'wizard'                  => array
			(
				'stylepicker'         => array('TableMetaModelDca','getPanelpicker')
			),
		),
	)
);

?>