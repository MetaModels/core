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

$GLOBALS['TL_DCA']['tl_metamodel_rendersetting'] = array
(
	'config' => array
	(
		'dataContainer'               => 'General',
		'ptable'                      => 'tl_metamodel_rendersettings',
		'switchToEdit'                => true,
		'enableVersioning'            => false,
	),

	'dca_config'                      => array
	(
		'data_provider'               => array
		(
			'parent'                  => array
			(
				'source'              => 'tl_metamodel_rendersettings'
			)
		),
		'childCondition'              => array
		(
			array(
				'from'                => 'tl_metamodel_rendersettings',
				'to'                  => 'tl_metamodel_rendersetting',
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
			'tl_metamodel_rendersetting' => array
			(
				'setOn' => array
				(
					array
					(
						'property'    => 'pid',
						'remote'      => 'id',
					),
				),
				'filter'              => array
				(
					array
					(
						'property'    => 'pid',
						'operation'   => '=',
						'remote'      => 'id',
					)
				)
			)
		),
		'child_list'                  => array
		(
			'tl_metamodel_rendersetting' => array
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
			'child_record_callback'   => array('MetaModels\Dca\RenderSetting', 'drawSetting'),
		),

		'global_operations' => array
		(
			'addall' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addall'],
				'href'                => 'key=rendersetting_addall',
				'class'               => 'header_add_all rendersetting_add_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),

			'toggle' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
				'button_callback'     => array('MetaModels\Dca\RenderSetting', 'toggleIcon')
			)
		)
	),

	'palettes' => array
	(
		'__selector__' => array('attr_id')
	),

	'metapalettes' => array
	(
		'default' => array
		(
			'title' => array('attr_id', 'template')
		),
	),

	// Fields
	'fields' => array
	(
		'attr_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['attr_id'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('MetaModels\Dca\RenderSetting', 'getAttributeNames'),
			'eval'                    => array(
				'doNotSaveEmpty'      => true,
				'alwaysSave'          => true,
				'submitOnChange'      => true,
				'includeBlankOption'  => true,
				'mandatory'           => true,
				'chosen'              => true,
				'tl_class'            => 'w50'
			),
		),

		'template' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['template'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('MetaModels\Dca\RenderSetting', 'getTemplates'),
			'eval'                    => array
			(
				'tl_class'            => 'w50',
				'chosen'              => true,
				'includeBlankOption'  => true,
			)
		),

		'sorting' => array(),
	)
);

