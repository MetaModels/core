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

/**
 * Table tl_metamodel_attribute
 */

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting'] = array
(
	'config' => array
	(
		'dataContainer'               => 'General',
		'switchToEdit'                => false,
		'enableVersioning'            => false,
		'oncreate_callback'           => array(array('TableMetaModelFilterSetting', 'create_callback')),
		'palettes_callback'           => array(array('TableMetaModelFilterSetting', 'preparePalettes')),
		'tablename_callback'          => array(array('TableMetaModelFilterSetting', 'loadTableCallback')),
	),
	'dca_config'                      => array
	(
		'data_provider'               => array
		(
			'parent'                  => array
			(
				'source'              => 'tl_metamodel_filtersetting'
			)
		),
		'childCondition'              => array
		(
			array(
				'from'                => 'self',
				'to'                  => 'self',
				'setOn'               => array
				(
					array(
						'to_field'    => 'pid',
						'from_field'  => 'id',
					),
					array(
						'to_field'    => 'fid',
						'from_field'  => 'fid',
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
			'self'                    => array
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
			'self'                    => array
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
		)
	),
	// List.
	'list' => array
	(
		'presentation' => array
		(
			'breadcrumb_callback'     => array('MetaModelBreadcrumbBuilder', 'generateBreadcrumbItems'),
		),

		'sorting' => array
		(
			'mode'                    => 5,
			'fields'                  => array('attr_id'),
			'panelLayout'             => 'filter,limit',
			'headerFields'            => array('type', 'attr_id'),
			'flag'                    => 1,
			'icon'                    => 'system/modules/metamodels/html/filter_and.png',
			'paste_button_callback'   => array('TableMetaModelFilterSetting', 'pasteButton'),
		),

		'label' => array
		(
			'fields'                  => array('type', 'attr_id', 'urlparam', 'comment'),
			'format'                  => '%s',
			'label_callback'          => array('TableMetaModelFilterSetting', 'drawSetting')
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
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),

			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
				'button_callback'     => array('TableMetaModelFilterSetting', 'toggleIcon')
			)
		)
	),

	'palettes' => array
	(
		'__selector__' => array('type')
	),

	'metapalettes' => array
	(
		'default' => array
		(
			'title' => array('type', 'enabled', 'comment'),
		),

		'_attribute_ extends default' => array
		(
			'config' => array('attr_id')
		),

		// base rules shipped with metamodels.
		'conditionor extends default' => array
		(
			'config' => array('stop_after_match')
		),

		'idlist extends default' => array
		(
			'+config' => array('items'),
		),

		'simplelookup extends _attribute_' => array
		(
			'+config' => array('urlparam', 'allow_empty', 'predef_param', 'label', 'template', 'defaultid', 'blankoption', 'onlyused', 'onlypossible'),
		),

		'customsql extends default' => array
		(
			'+config' => array('customsql'),
		),

		'simplelookup_translated extends _simplelookup_' => array
		(
			'+config' => array('all_langs'),
		),
	),

	'metasubselectpalettes' => array
	(
		'attr_id' => array
		(
		)
	),

	'simplelookup_palettes' => array
	(
		'_translated_' => array
		(
			'all_langs'
		)
	),
	// Fields
	'fields' => array
	(
		'fid' => array
		(
			// keep this empty but keep it here!
			// needed for act=copy in DC_Table, as otherwise the fid value will not be copied.
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['fid'],
		),

		'type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['type'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModelFilterSetting', 'getSettingTypes'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames'],
			'eval'                    => array
			(
				'doNotSaveEmpty'      => true,
				'alwaysSave'          => true,
				'submitOnChange'      => true,
				'includeBlankOption'  => true,
				'mandatory'           => true,
				'tl_class'            => 'w50',
				'chosen'              => true
			),
		),

		'enabled' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['enabled'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'alwaysSave'          => true,
				'tl_class'            => 'w50 m12',
			),
		),

		'comment'                     => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['comment'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('tl_class' => 'clr long')
		),

		'attr_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['attr_id'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModelFilterSetting', 'getAttributeNames'),
			'eval'                    => array
			(
				'doNotSaveEmpty'      => true,
				'alwaysSave'          => true,
				'submitOnChange'      => true,
				'includeBlankOption'  => true,
				'mandatory'           => true,
				'tl_class'            => 'w50',
				'chosen'              => true
			),
			'load_callback'           => array(array('TableMetaModelFilterSetting', 'attrIdToName')),
			'save_callback'           => array(array('TableMetaModelFilterSetting', 'nameToAttrId')),
		),

		'all_langs' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['all_langs'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'alwaysSave'          => true,
				'tl_class'            => 'w50 m12 cbx',
			),
		),

		'items' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['items'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array
			(
				'doNotSaveEmpty'      => true,
				'alwaysSave'          => true,
				'mandatory'           => true,
			),
		),

		'urlparam' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['urlparam'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
			)
		),

		'predef_param' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['predef_param'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'alwaysSave'          => true,
				'tl_class'            => 'w50 m12',
			),
		),

		'customsql' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['customsql'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array
			(
				'allowHtml'           => true,
				'rte'                 => 'codeMirror|sql',
				'class'               => 'monospace',
				'helpwizard'          => true,
			),
			'explanation'         => 'customsql'
		),
		'allow_empty' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['allow_empty'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'alwaysSave'          => true,
				'tl_class'            => 'w50 m12',
			),
		),
		'stop_after_match' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['stop_after_match'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'alwaysSave'          => true,
				'tl_class'            => 'w50 m12',
			),
		),
		'label' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['label'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'tl_class'            => 'clr w50',
			),
		),
		'template' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['template'],
			'default'                 => 'mm_filteritem_checkbox',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModelFilterSetting', 'getSubTemplates'),
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'chosen'              => true
			),
		),
		'blankoption'                 => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['blankoption'],
			'exclude'                 => true,
			'default'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'w50 clr',
			),
		),
		'onlyused'                    => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['onlyused'],
			'exclude'                 => true,
			'default'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
			),
		),
		'onlypossible'                => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['onlypossible'],
			'exclude'                 => true,
			'default'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
			),
		)
	)
);

