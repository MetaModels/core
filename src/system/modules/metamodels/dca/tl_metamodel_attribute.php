<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Table tl_metamodel_attribute
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute'] = array_replace_recursive(array
(
	'config' => array
	(
		'dataContainer'               => 'General',
		'ptable'                      => 'tl_metamodel',
		'switchToEdit'                => false,
		'enableVersioning'            => false,
		'onload_callback'             => array
		(
			array('TableMetaModelAttribute', 'onLoadCallback')
		),
		'ondelete_callback'           => array
		(
			array('TableMetaModelAttribute', 'onDeleteCallback')
		),
		'onmodel_beforeupdate'        => array
		(
			array('TableMetaModelAttribute', 'onModelBeforeUpdateCallback')
		),
		'onsave_callback'             => array
		(
			array('TableMetaModelAttribute', 'onSaveCallback')
		),
	),

	'list' => array
	(
		'presentation' => array
		(
			'breadcrumb_callback'     => array('MetaModelBreadcrumbBuilder', 'generateBreadcrumbItems'),
		),
		
		'sorting' => array
		(
			'disableGrouping'         => true,
			'mode'                    => 4,
			'panelLayout'             => 'filter,limit',
			'headerFields'            => array('name', 'tableName', 'tstamp', 'translated', 'supvariants', 'varsupport'),
			'flag'                    => 1,
			'child_record_callback'   => array('TableMetaModelAttribute', 'renderField')
		),

		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s'
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
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			/*
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			*/

			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),

		)
	),

	'metapalettes' => array
	(
		// Initial palette with only the type to be selected.
		'default' => array
		(
			'title' => array('type')
		),

		// Base palette for MetaModelAttribute derived types.
		'_base_ extends default'      => array
		(
			'+title'                  => array('colname', 'name', 'description'),
			'advanced'                => array(':hide', 'isvariant', 'mandatory', 'isunique', 'hasdefault'),
			'metamodeloverview'       => array('sortingField', 'filteredField', 'searchableField'),
			'backenddisplay'          => array('titleField', 'width50', 'insertBreak'),
		),
		// Default palette for MetaModelAttributeSimple derived types.
		// WARNING: even though it is empty, we have to keep it as otherwise
		// metapalettes will have no way for deriving the palettes. - They need the index.
				'_simpleattribute_ extends _base_' => array
		(
		),
		// Default palette for MetaModelAttributeComplex derived types.
		// WARNING: even though it is empty, we have to keep it as otherwise
		// metapalettes will have no way for deriving the palettes. - They need the index.
		'_complexattribute_ extends _base_' => array
		(
		),
	),

	'metasubpalettes' => array
	(
		// Displaying in backend.
		'insertBreak'                 => array('legendTitle','legendHide'),

		'sortingField'                => 'groupingMode',
		'showImage'                   => 'imageSize',
		'format'                      => 'formatFunction,formatStr',
		'limitItems'                  => 'items,childrenSelMode,parentFilter',
		'customFiletree'              => 'uploadFolder,validFileTypes,filesOnly',
		'editGroups'                  => 'editGroups',
		'rte'                         => 'rte_editor',
		'multiple'                    => 'sortBy',
	),


	// Palettes.
	'palettes' => array
	(
		'__selector__' => array('type')
	),
	// Fields.
	'fields' => array
	(
		'type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['type'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['typeOptions'],
			'eval'                    => array
			(
				'includeBlankOption'  => true,
				'doNotSaveEmpty'      => true,
				'alwaysSave'          => true,
				'submitOnChange'      => true,
				'mandatory'           => true,
				'tl_class'            => 'w50',
				'chosen'              => 'true'
			),
			'options_callback'        => array('TableMetaModelAttribute', 'fieldTypesCallback'),
		),

		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name'],
			'exclude'                 => true,
			'eval'                    => array
			(
				'tl_class'            => 'clr'
			),
			'load_callback'           => array
			(
				array('TableMetaModelAttribute', 'decodeNameAndDescription')
			),
			'save_callback'           => array
			(
				array('TableMetaModelAttribute', 'encodeNameAndDescription')
			)
		),

		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['description'],
			'exclude'                 => true,
			'eval'                    => array
			(
				'tl_class'            => 'clr'
			),
			'load_callback'           => array
			(
				array('TableMetaModelAttribute', 'decodeNameAndDescription')
			),
			'save_callback'           => array
			(
				array('TableMetaModelAttribute', 'encodeNameAndDescription')
			)
		),

		// AVOID: doNotCopy => true, as child records won't be copied when copy metamodel.
		'colname' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['colname'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => true,
				'maxlength'           => 64,
				'tl_class'            => 'w50'
				),
		),

		'isvariant' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isvariant'],
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'submitOnChange'      => true,
				'tl_class'            => 'cbx w50'
			)
		),

		'isunique' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isunique'],
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'cbx w50'
			),
		),
	)
), (array)$GLOBALS['TL_DCA']['tl_metamodel_attribute']);

