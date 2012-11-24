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
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
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
	)
);

?>