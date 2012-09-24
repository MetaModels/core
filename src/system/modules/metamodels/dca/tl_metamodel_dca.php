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
            'fields'                  => array('name'),
            'panelLayout'             => 'filter,limit',
            'headerFields'            => array('name'),
            'flag'                    => 1,
		),

        'label' => array
        (
            'fields'                  => array('name'),
            'format'                  => '%s',
            'label_callback'          => array('TableMetaModelDca', 'drawSetting')
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
				'icon'                => 'system/modules/metamodels/html/palettesetting.png',
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
				'isdefault'
			),
		)
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

		'isdefault' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['isdefault'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50 m12')
		),
	)
);

?>