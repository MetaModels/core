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
		// TODO: change callbacks to event handlers.
		'ondelete_callback'           => array
		(
			array('MetaModels\Dca\Attribute', 'onDeleteCallback')
		),
		'onmodel_beforeupdate'        => array
		(
			array('MetaModels\Dca\Attribute', 'onModelBeforeUpdateCallback')
		),
		'onsave_callback'             => array
		(
			array('MetaModels\Dca\Attribute', 'onSaveCallback')
		),
	),

	'dca_config'                      => array
	(
		'data_provider'               => array
		(
			'parent'                  => array
			(
				'source'              => 'tl_metamodel'
			)
		),
		'childCondition'              => array
		(
			array(
				'from'                => 'tl_metamodel',
				'to'                  => 'tl_metamodel_attribute',
				'setOn'               => array
				(
					array(
						'to_field'    => 'pid',
						'from_field'  => 'id',
					),
				),
				'filter'              => array
				(
					array
					(
						'local'       => 'pid',
						'remote'      => 'id',
						'operation'   => '=',
					),
				),
				'inverse'             => array
				(
					array
					(
						'local'       => 'pid',
						'remote'      => 'id',
						'operation'   => '=',
					),
				)
			)
		),
	),

	'list' => array
	(
		'sorting' => array
		(
			'disableGrouping'         => true,
			'mode'                    => 4,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'filter,limit',
			'headerFields'            => array('name', 'tableName', 'tstamp', 'translated', 'varsupport'),
			'flag'                    => 1,
			// TODO: change callbacks to event handlers.
			'child_record_callback'   => array('MetaModels\Dca\Attribute', 'renderField')
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
				'attributes'          => sprintf(
					'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
					$GLOBALS['TL_LANG']['MSC']['deleteConfirm']
				)
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
			'advanced'                => array(':hide', 'isvariant', 'isunique'),
			'metamodeloverview'       => array(),
			'backenddisplay'          => array(),
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

	// Palettes.
	'palettes' => array
	(
		'__selector__' => array('type')
	),
	// Fields.
	'fields' => array
	(
		'tstamp' => array
		(
		),

		'sorting' => array
		(
			'sorting'                 => true,
		),

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
		),

		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name'],
			'exclude'                 => true,
			'eval'                    => array
			(
				'tl_class'            => 'clr'
			),
		),

		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['description'],
			'exclude'                 => true,
			'eval'                    => array
			(
				'tl_class'            => 'clr'
			),
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
