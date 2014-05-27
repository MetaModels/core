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

$GLOBALS['TL_DCA']['tl_metamodel_dcasetting'] = array
(
	'config' => array
	(
		'dataContainer'               => 'General',
		'switchToEdit'                => true,
		'enableVersioning'            => false,
		// TODO: change callbacks to event handlers.
		'onmodel_update'              => array
		(
			array('MetaModels\Dca\DcaSetting', 'onModelUpdatedCallback')
		),
		'onmodel_beforeupdate'        => array
		(
			array('MetaModels\Dca\DcaSetting', 'onModelUpdatedCallback')
		),
	),

	'dca_config'                      => array
	(
		'data_provider'               => array
		(
			'default'                 => array
			(
				'source'              => 'tl_metamodel_dcasetting'
			),

			'parent'                  => array
			(
			'source'              => 'tl_metamodel_dca'
			),

			'root'                  => array
			(
			'source'              => 'tl_metamodel_dca'
			),
		),
		'childCondition'              => array
		(
			array(
				'from'                => 'tl_metamodel_dca',
				'to'                  => 'tl_metamodel_dcasetting',
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
				)
			)
		),
		'rootEntries'                 => array
		(
			'tl_metamodel_dcasetting' => array
			(
				'setOn' => array
				(
					array
					(
						'property'    => 'pid',
						'value'       => '0'
					),
				),
				'filter'              => array
				(
					array
					(
						'property'    => 'pid',
						'operation'   => '=',
						'value'       => '0'
					)
				)
			)
		),
		'child_list'                  => array
		(
			'tl_metamodel_dcasetting' => array
			(
				'fields'              => array
				(
					'type',
					'attr_id',
					'urlparam',
					'comment'
				),
				'format'              => '%s %s',
			),
		),
	),

	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'limit',
			'headerFields'            => array('name'),
		),

		'global_operations' => array
		(
			'addall' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall'],
				'href'                => 'act=dca_addall',
				'class'               => 'header_add_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => sprintf(
					'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
					$GLOBALS['TL_LANG']['MSC']['deleteConfirm']
				)
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['toggle'],
				'icon'                => 'visible.gif',
				'toggleProperty'      => 'published',
			),
			'conditions' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['conditions'],
				'href'                => 'table=tl_metamodel_dcasetting_condition',
				'icon'                => 'system/modules/metamodels/html/dca_subpalette.png',
				'idparam'             => 'pid'
			),
		)
	),

	'palettes' => array
	(
		'__selector__' => array('dcatype', 'attr_id')
	),

	'metapalettes' => array
	(
		'default' => array
		(
			'title' => array('dcatype'),
		),
	),

	'metasubselectpalettes' => array
	(
		'dcatype' => array
		(
			'attribute' => array
			(
				'title' => array(
					'attr_id'
				),
				'functions' => array('readonly'),
				'advanced'  => array()
			),
			'legend' => array
			(
				'title' => array
				(
					'legendtitle',
					'legendhide'
				)
			)
		),

		'attr_id' => array
		(
			/*
			 * Core legends:
			 * * title
			 * * backend
			 * * config
			 * * advanced
			 *
			 * Core fields:
			 * * tl_class           css class to use in backend.
			 * * flag               search flag to override.
			 * * mandatory          mandatory
			 * * chosen
			 * * filterable         can be filtered (in backend)
			 * * sortable           can be sorted (in backend)
			 * * searchable         can be searched (in backend)
			 * * allowHtml          do not strip html content.
			 * * preserveTags       do not encode html tags.
			 * * decodeEntities     do decode HTML entities.
			 * * rte                enable richtext editor on this
			 * * rows               amount of rows in longtext and tables.
			 * * cols               amount of columns in longtext and tables.
			 * * trailingSlash      allow trailing slash, 2 => do nothing, 1 => add one on save, 0 => strip it on save.
			 * * spaceToUnderscore  if true any whitespace character will be replaced by an underscore.
			 * * includeBlankOption if true a blank option will be added to the options array.
			 */
		)
	),

	'fields' => array
	(
		'sorting' => array(),

		'dcatype' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatype'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => array('attribute', 'legend'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatypes'],
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'includeBlankOption'  => true,
				'submitOnChange'      => true,
			)
		),
		'attr_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['attr_id'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array(
				'tl_class'            => 'w50',
				'doNotSaveEmpty'      => true,
				'alwaysSave'          => true,
				'includeBlankOption'  => true,
				'mandatory'           => true,
			),
		),
		'tl_class' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['tl_class'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'tl_class'            => 'long wizard',
			),
		),
		'legendhide' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendhide'],
			'exclude'               => true,
			'inputType'             => 'checkbox',
			'eval'                  => array
			(
				'tl_class'          => 'clr m12'
			)
		),
		'legendtitle' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendtitle'],
			'exclude'               => true,
		),
		'mandatory' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['mandatory'],
			'exclude'               => true,
			'inputType'             => 'checkbox',
			'eval' => array
			(
				'tl_class'          => 'w50',
			)
		),
		'alwaysSave' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['alwaysSave'],
			'exclude'               => true,
			'inputType'             => 'checkbox',
			'eval' => array
			(
				'tl_class'          => 'w50',
			)
		),
		'filterable' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['filterable'],
			'exclude'               => true,
			'inputType'             => 'checkbox',
			'eval' => array
			(
				'tl_class'          => 'w50',
			)
		),
		'sortable' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortable'],
			'exclude'               => true,
			'inputType'             => 'checkbox',
			'eval' => array
			(
				'tl_class'          => 'm12 w50',
			)
		),
		'searchable' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['searchable'],
			'exclude'               => true,
			'inputType'             => 'checkbox',
			'eval'                  => array
			(
				'tl_class'          => 'w50',
			)
		),
		'flag' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['flag'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'),
			'eval'                    => array
			(
				'tl_class'           => 'w50',
				'includeBlankOption' => true
			),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']
		),

		/*
		 * The following settings are predefined as they apply for a huge amount of attribute types.
		 * Hence we define them in the core.
		 * If others are needed, that apply to at least 2-3 attribute extensions, consider adding it in the core.
		 */
		'chosen' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['chosen'],
			'exclude'               => true,
			'inputType'             => 'checkbox',
			'eval'                  => array
			(
				'tl_class'          => 'w50 m12'
			)
		),

		'allowHtml' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['allowHtml'],
			'exclude'               => true,
			'inputType'             => 'checkbox',
			'eval'                  => array
			(
				'tl_class'          => 'w50',
			)
		),

		'preserveTags' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['preserveTags'],
			'exclude'               => true,
			'inputType'             => 'checkbox',
			'eval'                  => array
			(
				'tl_class'          => 'w50',
			)
		),

		'decodeEntities' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['decodeEntities'],
			'exclude'               => true,
			'inputType'             => 'checkbox',
			'eval'                  => array
			(
				'tl_class'          => 'w50',
			)
		),

		'rte' => array
		(
			'label'                  => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['rte'],
			'exclude'                => true,
			'inputType'              => 'select',
			'default'                => 'tinyMCE',
			'eval'                   => array
			(
				'tl_class'           => 'm12',
				'includeBlankOption' => true,
			)
		),

		'rows' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['rows'],
			'exclude'               => true,
			'inputType'             => 'text',
			'eval'                  => array
			(
				'tl_class'          => 'w50',
				'rgxp'              => 'digit'
			)
		),

		'cols' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['cols'],
			'exclude'               => true,
			'inputType'             => 'text',
			'eval'                  => array
			(
				'tl_class'          => 'w50',
				'rgxp'              => 'digit'
			)
		),

		'trailingSlash' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash'],
			'exclude'               => true,
			'inputType'             => 'select',
			'options'               => array(0, 1, 2),
			'default'               => 2,
			'reference'              => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash_options'],
			'eval'                  => array
			(
				'tl_class'          => 'w50 clr',
			)
		),

		'spaceToUnderscore' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['spaceToUnderscore'],
			'exclude'               => true,
			'inputType'             => 'checkbox',
			'eval'                  => array
			(
				'tl_class'          => 'w50 m12',
			)
		),

		'includeBlankOption' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['includeBlankOption'],
			'exclude'               => true,
			'inputType'             => 'checkbox',
			'eval'                  => array
			(
				'tl_class'          => 'clr m12',
			)
		),
		'submitOnChange' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['submitOnChange'],
			'exclude'               => true,
			'inputType'             => 'checkbox',
			'eval'                  => array
			(
				'tl_class'          => 'clr m12',
			)
		),

		'readonly' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['readonly'],
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
			)
		)
	)
);
