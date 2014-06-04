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
 * Table tl_metamodel_dcasetting_condition
 */

$GLOBALS['TL_DCA']['tl_metamodel_dcasetting_condition'] = array
(
	'config' => array
	(
		'dataContainer'               => 'General',
		'switchToEdit'                => false,
		'enableVersioning'            => false,
	),
	'dca_config'                      => array
	(
		'data_provider'               => array
		(
			'root'                    => array
			(
				'source'              => 'tl_metamodel_dcasetting_condition'
			),
			'parent'                  => array
			(
				'source'              => 'tl_metamodel_dcasetting',
			),
		),
		'childCondition'              => array
		(
			array(
				'from'                => 'tl_metamodel_dcasetting',
				'to'                  => 'tl_metamodel_dcasetting_condition',
				'setOn'               => array
				(
					array(
						'to_field'    => 'settingId',
						'from_field'  => 'id',
					)
				),
				'filter'              => array
				(
					array
					(
						'local'       => 'settingId',
						'remote'      => 'id',
						'operation'   => '=',
					),
				)
			),
			array(
				'from'                => 'tl_metamodel_dcasetting_condition',
				'to'                  => 'tl_metamodel_dcasetting_condition',
				'setOn'               => array
				(
					array(
						'to_field'    => 'pid',
						'from_field'  => 'id',
					),
					array(
						'to_field'    => 'settingId',
						'from_field'  => 'settingId',
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
			'tl_metamodel_dcasetting_condition'  => array
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
			'tl_metamodel_dcasetting_condition' => array
			(
				'fields'              => array
				(
					'condition',
					'attr_id',
					'comment',
					'enabled'
				),
				'format'              => '%s %s',
			),
		)
	),
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 5,
			'fields'                  => array('attr_id'),
			'headerFields'            => array('type', 'attr_id'),
			'flag'                    => 1,
			'icon'                    => 'system/modules/metamodels/assets/images/icons/filter_and.png',
		),

		'label' => array
		(
			'fields'                  => array('type', 'attr_id', 'comment'),
			'format'                  => '%s %s %s',
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
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset()"',
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => sprintf(
					'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
					$GLOBALS['TL_LANG']['MSC']['deleteConfirm']
				)
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),

			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['toggle'],
				'icon'                => 'visible.gif',
				'toggleProperty'      => 'enabled',
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
			'+title' => array('type', 'enabled', 'comment'),
		),

		'_attribute_ extends default' => array
		(
			'+config' => array('attr_id')
		),

		'conditionor extends default' => array
		(
		),

		'conditionand extends default' => array
		(
		),

		'conditionpropertyvalueis extends _attribute_' => array
		(
			'+config' => array('value')
		),

		'conditionpropertyvisible extends _attribute_' => array
		(
		),
	),

	'metasubselectpalettes' => array
	(
		'attr_id' => array
		(
		)
	),

	'fields' => array
	(
		'settingId' => array
		(
			// Keep this empty but keep it here!
			// needed for act=copy in DC_Table, as otherwise the fid value will not be copied.
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['fid'],
		),

		'type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['type'],
			'exclude'                 => true,
			'inputType'               => 'select',
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['enabled'],
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
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['comment'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('tl_class' => 'clr long')
		),

		'attr_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['attr_id'],
			'exclude'                 => true,
			'inputType'               => 'select',
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

		'value' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['value'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array
			(
				'alwaysSave'          => true,
				'includeBlankOption'  => true,
				'tl_class'            => 'w50',
				'chosen'              => true
			),
		),
	)
);
