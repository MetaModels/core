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

$GLOBALS['TL_DCA']['tl_metamodel_rendersettings'] = array
(
	'config' => array
	(
		'dataContainer'               => 'General',
		'ptable'                      => 'tl_metamodel',
		'ctable'                      => 'tl_metamodel_rendersetting',
		'switchToEdit'                => false,
		'enableVersioning'            => false,
		'onload_callback'             => array
		(
			array('TableMetaModelRenderSettings', 'onLoadCallback')
		),
	),

	// List
	'list' => array
	(
		'presentation' => array
		(
			'breadcrumb_callback'     => array('MetaModelBreadcrumbBuilder', 'generateBreadcrumbItems'),
		),
		
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
			'label_callback'          => array('TableMetaModelRenderSettings', 'drawSetting')
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
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'settings' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['settings'],
				'href'                => 'table=tl_metamodel_rendersetting',
				'icon'                => 'system/modules/metamodels/html/render_setting.png',
			),
		)
	),

	'metapalettes' => array
	(
		'default' => array
		(
			'title' => array('name', 'isdefault'),
			'general' => array('template', 'format', 'jumpTo'),
		),
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
		),
		'isdefault' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['isdefault'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'m12 w50 cbx')
		),
		'template' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['template'],
			'default'                 => 'metamodel_prerendered',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModelRenderSettings','getTemplates'),
			'eval'                    => array('tl_class'=>'w50')
		),
		'format' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['format'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => array('html5', 'xhtml', 'text'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['formatOptions'],
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50')
		),
		'jumpTo' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo'],
			'exclude'                 => true,
			'minCount'                => 1,
			'maxCount'                => 1,
			'disableSorting'          =>'1',
			'inputType'               => 'multiColumnWizard',
			'load_callback'           => array(array('TableMetaModelRenderSettings', 'prepareMCW')),
			'save_callback'           => array(array('TableMetaModelRenderSettings', 'saveMCW')),
			'eval' => array(
				'style'               => 'width:100%;',
				'columnFields' => array(
					'langcode' => array(
						'label'                    => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo_language'],
						'exclude'                  =>true,
						'inputType'                =>'justtextoption',
						'options'                  =>array('xx' => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo']['allLanguages']),
						'eval'                     =>array('valign'=>'center')
					),
					'value' => array(
						'label'                    => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo_page'],
						'exclude'                  => true,
						'inputType'                => 'text',
						'wizard'                   => array(array('tl_metamodel_rendersettings', 'pagePicker')),
						'eval'                     => array('style'=>'width:200px;')
					),
					'filter' => array(
						'label'                    => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo_filter'],
						'exclude'                  => true,
						'inputType'                => 'select',
						'options_callback'         => array('TableMetaModelRenderSettings', 'getFilterSettings'),
						'eval'                     => array
						(
							'style'                => 'width:200px;',
							'includeBlankOption'   => true
						)
					),
				),
				'buttons'               => array('copy' => false, 'delete' => false, 'up' => false, 'down' => false),
				'tl_class'              => 'clr',
			)
		),
	),
);

class tl_metamodel_rendersettings extends backend
{
		/**
	 * Return the link picker wizard
	 * @param DataContainer
	 * @return string
	 */
	public function pagePicker(DataContainer $dc)
	{
		return ' ' . $this->generateImage('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="vertical-align:top;cursor:pointer" onclick="Backend.pickPage(\'ctrl_' . $dc->inputName . '\')"');
	}

}

