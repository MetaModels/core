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
 * Table tl_metamodel_attribute
 */

$GLOBALS['TL_DCA']['tl_metamodel_dcasetting'] = array
(
	'config' => array
	(
//		'dataContainer'               => 'Table',
		'dataContainer'               => 'General',
		'ptable'                      => 'tl_metamodel_dca',
		'switchToEdit'                => true,
		'enableVersioning'            => false,
		'onmodel_update'              => array
		(
			array('TableMetaModelDcaSetting', 'onModelUpdatedCallback')
		),
		'onmodel_beforeupdate'        => array
		(
			array('TableMetaModelDcaSetting', 'onModelUpdatedCallback')
		)

	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'limit',
			'headerFields'            => array('name'),
			'child_record_callback'   => array('TableMetaModelDcaSetting', 'drawSetting'),
		),

		'global_operations' => array
		(
			'addall' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall'],
				'href'                => 'key=dca_addall',
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
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
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
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
		)
	),

	'palettes' => array
	(
		'__selector__' => array('dcatype')
	),

	'metapalettes' => array
	(
		'default' => array
		(
			'title' => array('dcatype')
		),
	),

	'metasubselectpalettes' => array
	(
		'dcatype' => array
		(
			'attribute' => array
			(
				'attr_id',
				'tl_class'
			),
			'legend' => array
			(
				'legendtitle',
				'legendhide'
			)
		),
	),

	// Fields
	'fields' => array
	(
		'dcatype' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatype'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => array('attribute', 'legend'),
			'reference'               => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatypes'],
			'eval'                    => array
			(
				'tl_class'=>'w50',
				'includeBlankOption'  => true,
				'submitOnChange'      => true,
			)
		),

		'attr_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['attr_id'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('TableMetaModelDcaSetting', 'getAttributeNames'),
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
				'tl_class'            => 'w50 wizard',
			),
			'wizard'                  => array
			(
				'stylepicker'         => array('TableMetaModelDcaSetting','getStylepicker')
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
			'load_callback'         => array
			(
				array('TableMetaModelDcaSetting', 'decodeLegendTitle')
			),
			'save_callback'         => array
			(
				array('TableMetaModelDcaSetting', 'encodeLegendTitle')
			)
		)

	)
);

?>