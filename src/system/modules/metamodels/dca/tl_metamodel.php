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

$this->loadLanguageFile('languages');

/**
 * Table tl_metamodel
 */

$GLOBALS['TL_DCA']['tl_metamodel'] = array
(

	// Config
	'config' => array
	(
//		'dataContainer'               => 'Table',
		'dataContainer'               => 'General',

		'ctable'                      => array('tl_metamodel_attribute', 'tl_metamodel_filter', 'tl_metamodel_rendersettings', 'tl_metamodel_dca', 'tl_metamodel_dca_combine'),
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'onload_callback'             => array
		(
			array('TableMetaModel', 'onLoadCallback'),
		),
		'onsubmit_callback'           => array
		(
			array('TableMetaModel', 'onSubmitCallback'),
		),
		'ondelete_callback'           => array
		(
			array('TableMetaModel', 'onDeleteCallback')
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('name'),
			'flag'                    => 1,
			'panelLayout'             => 'filter;search,limit'
		),

		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s',
			'label_callback'          => array('TableMetaModel','getRowLabel')
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
/*
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['edit'],
				'icon'                => 'edit.gif',
				'attributes'          => 'class="contextmenu"',
				'button_callback'     => array('TableMetaModel', 'buttonCallbackItemEdit')
			),
			'editheader' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'header.gif',
				'attributes'          => 'class="edit-header"'
			),
*/
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
				'button_callback'     => array('TableMetaModel', 'buttonCallback')
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'button_callback'     => array('TableMetaModel', 'buttonCallback'),
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'

			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),

			'fields' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['fields'],
				'href'                => 'table=tl_metamodel_attribute',
				'icon'                => 'system/modules/metamodels/html/fields.png',
				'button_callback'     => array('TableMetaModel', 'buttonCallback')
			),

			'rendersettings' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['rendersettings'],
				'href'                => 'table=tl_metamodel_rendersettings',
				'icon'                => 'system/modules/metamodels/html/rendersettings.png',
				'button_callback'     => array('TableMetaModel', 'buttonCallback')
			),

			'dca' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['dca'],
				'href'                => 'table=tl_metamodel_dca',
				'icon'                => 'system/modules/metamodels/html/palettes.png',
				'button_callback'     => array('TableMetaModel', 'buttonCallback')
			),

            'filter' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['filter'],
                'href'                => 'table=tl_metamodel_filter',
                'icon'                => 'system/modules/metamodels/html/filter.png',
                'button_callback'     => array('TableMetaModel', 'buttonCallback')
            ),

			'dca_combine' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['dca_combine'],
				'href'                => 'table=tl_metamodel_dca_combine&act=edit',
				'icon'                => 'system/modules/metamodels/html/dca_combine.png',
				'button_callback'     => array('TableMetaModel', 'buttonCallback')
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
				'tableName',
				'translated'
			),

			'advanced' => array
			(
				':hide',
				'varsupport'
			),

			'backend' => array
			(
				'rendertype',
/*
				'backendsection',
				'backendicon'
*/
			),
		)
	),

	// Subpalettes
	'metasubpalettes' => array
	(
		'translated' => array
		(
			'languages'
		),
	),
			/*
				0 Records are not sorted
				1 Records are sorted by a fixed field
				2 Records are sorted by a switchable field
				3 Records are sorted by the parent table
				4 Displays the child records of a parent record (see style sheets module)
				5 Records are displayed as tree (see site structure)
				6 Displays the child records within a tree structure (see articles module)
			*/
	'metasubselectpalettes' => array
	(
		'rendertype' => array
		(
			'standalone' => array
			(
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
			'mode_0'  => array('backendsection', 'backendicon'),
			'mode_1'  => array('backendsection', 'backendicon'),
			'mode_2'  => array('backendsection', 'backendicon'),
			'mode_3'  => array('backendsection', 'backendicon'),
			'mode_4'  => array(''), // TODO: select parent head fields here.
			'mode_5'  => array('backendsection', 'backendicon'),
			'mode_6'  => array('ptable', 'backendicon'),
		),
	),


	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>64, 'tl_class'=>'w50', 'unique' => true)
		),

		'tableName' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['tableName'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>64, 'doNotCopy'=>true, 'tl_class'=>'w50'),
			'save_callback'           => array
			(
				array('TableMetaModel', 'tableNameOnSaveCallback')
			)
		),

		'translated' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['translated'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'clr m12', 'submitOnChange' => true)
		),

		'languages' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['languages'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval' 			=> array
			(
				'columnFields' => array
				(
					'langcode' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel']['languages_langcode'],
						'exclude'               => true,
						'inputType'             => 'select',
						'options'               => $this->getLanguages(),
						'eval'                  => array
						(
							'style' => 'width:470px',
							'chosen'=> 'true'
						)
					),
					'isfallback' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel']['languages_isfallback'],
						'exclude'               => true,
						'inputType'             => 'checkbox',
						'eval'                  => array
						(
                            'style' => 'width:50px',
						)
					),
				),
			),
			'load_callback' => array
			(
				array('TableMetaModel', 'fixLangArray')
			),
			'save_callback' => array
			(
				array('TableMetaModel', 'unfixLangArray')
			)
		),

		'varsupport' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['varsupport'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'clr',
				'submitOnChange'      => true
			)
		),

		'rendertype' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['rendertype'],
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModel', 'getRenderTypes'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel']['rendertypes'],
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'submitOnChange'      => true,
				'includeBlankOption'  => true
			)
		),

		'ptable' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['ptable'],
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModel', 'getTables'),
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'submitOnChange'      => true,
				'includeBlankOption'  => true
			)
		),

		'mode' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['mode'],
			'inputType'               => 'select',
			'default'                 => '',
			'options_callback'        => array('TableMetaModel', 'getValidModes'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel']['modes'],
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
				'includeBlankOption'  => true,
				'tl_class'            => 'w50',
				'submitOnChange'      => true
			),
			'load_callback'           => array
			(
				array('TableMetaModel', 'modeLoad')
			),
			'save_callback'           => array
			(
				array('TableMetaModel', 'modeSave')
			)
		),

		'backendsection' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['backendsection'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'reference'               => &$GLOBALS['TL_LANG']['MOD'],
			'eval'                    => array
			(
				'includeBlankOption'  => true,
				'valign'              => 'top',
				'chosen'              => true,
			),
			'options_callback'        => array('TableMetaModel', 'backendSectionCallback'),
		),

		'backendicon' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['backendicon'],
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['backendcaption'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval' 			=> array
			(
				'columnFields' => array
				(
					'langcode' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel']['becap_langcode'],
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
						'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel']['becap_label'],
						'exclude'               => true,
						'inputType'             => 'text',
						'eval'                  => array
						(
							'style' => 'width:180px',
						)
					),
					'description' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel']['becap_description'],
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
	)
);

?>