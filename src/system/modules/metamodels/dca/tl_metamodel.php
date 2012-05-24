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
		'dataContainer'               => 'Table',
		'ctable'                      => array('tl_metamodel_attribute'),
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'onload_callback'             => array
		(
			array('TableMetaModel', 'onLoadCallback'),
		),
		'onsubmit_callback'             => array
		(
			array('TableMetaModel', 'onSubmitCallback'),
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
				'icon'                => 'system/modules/metamodels/html/fields.gif',
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
				'translated',
				'backendsection',
				'backendicon'
			),

			'advanced' => array
			(
				':hide',
				'ptable',
				'mode',
				'varsupport'
			),

			'display' => array
			(
				':hide',
				'format'
			),
		)
	),

	// Subpalettes
	'metasubpalettes' => array
	(
		'translated' => array
		(
			'languages'
		)
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>64, 'tl_class'=>'w50', 'unique' => true)
		),

		'tableName' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['tableName'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>64, 'doNotCopy'=>true, 'tl_class'=>'w50'),
			'save_callback'           => array
			(
				array('TableMetaModel', 'tableNameOnSaveCallback')
			)
		),

		'ptable' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['ptable'],
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModel', 'getTables'),
			'eval'                    => array('submitOnChange'=>true, 'includeBlankOption'=>true)
		),

		'mode' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['mode'],
			'inputType'               => 'select',
			'default'                 => '1',
			'options'                 => array('0', '1', '2', '3', '4', '5', '6'),
			/*
				0 Records are not sorted
				1 Records are sorted by a fixed field
				2 Records are sorted by a switchable field
				3 Records are sorted by the parent table
				4 Displays the child records of a parent record (see style sheets module)
				5 Records are displayed as tree (see site structure)
				6 Displays the child records within a tree structure (see articles module)
			*/
		),

		'translated' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['translated'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange' => true)
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
						'options'               => &$GLOBALS['TL_LANG']['LNG'],
						'eval'                  => array
						(
							'valign' => 'top',
							'style' => 'width:250px',
							'chosen'=>true
						)
					),
					'isfallback' => array
					(
						'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel']['languages_isfallback'],
						'exclude'               => true,
						'inputType'             => 'checkbox',
						'eval'                  => array
						(
							'valign' => 'top',
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
			'eval'                    => array('submitOnChange' => true)
		),

		// TODO: add support to list this metamodel in the backend here. This can be done by adding the following fields:
		// section    - the top level section id in the backend (i.e. system)
		// icon       - file picker for an image which then has to be resized to 16x16 for use in the backend list.
		'backendsection' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel']['backendsection'],
			'exclude'               => true,
			'inputType'             => 'select',
			'reference'               => &$GLOBALS['TL_LANG']['MOD'],
			'eval'                  => array
			(
				'includeBlankOption' => true,
				'valign' => 'top',
				'style' => 'width:250px',
				'chosen'=>true,
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
				'fieldType'=>'radio',
				'files'=>true,
				'filesOnly'=>true,
				//'mandatory'=>true,
				'extensions' => 'jpg,jpeg,gif,png,tif,tiff'
			)
		),

		'format' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['format'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => array('allowHtml'=>true)
		),
	)
);

?>