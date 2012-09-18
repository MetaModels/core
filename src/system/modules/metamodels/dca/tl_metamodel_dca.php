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

/**
 * Table tl_metamodel_dca
 */

$GLOBALS['TL_DCA']['tl_metamodel_dca'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'General',
		'ptable'                      => 'tl_metamodel',
		'ctable'                      => 'tl_metamodel_dcasetting',
		'switchToEdit'                => false,
		'enableVersioning'            => false,
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'limit',
			'headerFields'            => array('name'),
			'flag'                    => 11,
		),

		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s',
//			'label_callback'          => array('TableMetaModelRenderSettings', 'drawSetting')
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
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'settings' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['settings'],
				'href'                => 'table=tl_metamodel_dcasetting',
				'icon'                => 'system/modules/metamodels/html/dca.png',
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
			),
			'backend' => array
			(
				'be_template',
				'fe_template',
			),
			'advanced' => array
			(
				':hide',
				'be_groups',
				'fe_groups',
			),
		)
	),

	// Subpalettes
	'metasubpalettes' => array
	(
	),

	'metasubselectpalettes' => array
	(
		'rendertype' => array
		(
			'standalone' => array
			(
				'backendsection',
				'backendicon'
			),
			'ctable' => array
			(
				'ptable',
				'mode'
			)
		),
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>64, 'tl_class'=>'w50')
		),

		'be_groups' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['be_groups'],
			'inputType'               => 'checkboxWizard',
			'foreignKey'              => 'tl_user_group.name',
			'eval'                    => array
			(
				'multiple'            => true,
				'tl_class'            => 'w50',
			)
		),

		'fe_groups' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_groups'],
			'inputType'               => 'checkboxWizard',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array
			(
				'multiple'            => true,
				'tl_class'            => 'w50',
			)
		),

		'be_template' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['be_template'],
			'inputType'               => 'select',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
			)
		),

		'fe_template' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_template'],
			'inputType'               => 'select',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array
			(
				'tl_class'            => 'w50',
			)
		),
	)
);

?>