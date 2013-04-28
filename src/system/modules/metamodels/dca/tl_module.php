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

$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = array('TableModule', 'buildFilterParams');

/**
 * Add palettes to tl_module
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['metamodel_list']  = '{title_legend},name,headline,type;{config_legend},metamodel,perPage,metamodel_use_limit;{mm_filter_legend},metamodel_sortby,metamodel_sortby_direction,metamodel_filtering,metamodel_filterparams;{template_legend:hide},metamodel_layout,metamodel_rendersettings,metamodel_noparsing;{protected_legend:hide},protected;{expert_legend:hide},metamodel_donotindex,guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['metamodels_frontendfilter'] = '{title_legend},name,headline,type;{mm_filter_legend},metamodel_jumpTo,metamodel,metamodel_filtering,metamodel_fef_params,metamodel_fef_autosubmit,metamodel_fef_template;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'metamodel_use_limit';

// Insert new Subpalettes after position 1
array_insert($GLOBALS['TL_DCA']['tl_module']['subpalettes'], 1, array
	(
		'metamodel_use_limit' => 'metamodel_offset,metamodel_limit',
	)
);

/**
 * Add fields to tl_module
 */
array_insert($GLOBALS['TL_DCA']['tl_module']['fields'] , 1, array
(
	'metamodel' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'foreignKey'              => 'tl_metamodel.name',
		'eval' => array
		(
			'mandatory'           => true,
			'submitOnChange'      => true,
			'includeBlankOption'  => true
		),
		'wizard' => array
		(
			array('TableModule', 'editMetaModel')
		)
	),

	'metamodel_layout' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_layout'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'options_callback'        => array('TableModule', 'getModuleTemplates'),
		'eval'                    => array('tl_class'=>'w50')
	),

	'metamodel_use_limit' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_use_limit'],
		'exclude'                 => true,
		'inputType'               => 'checkbox',
		'eval'                    => array('submitOnChange'=> true, 'tl_class' => 'w50 m12'),
	),

	'metamodel_limit' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_limit'],
		'exclude'                 => true,
		'inputType'               => 'text',
		'eval'                    => array('rgxp'=>'digit', 'tl_class'=>'w50')
	),

	'metamodel_offset' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_offset'],
		'exclude'                 => true,
		'inputType'               => 'text',
		'eval'                    => array('rgxp' => 'digit', 'tl_class'=>'w50'),
	),

	'metamodel_sortby' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'options_callback'        => array('TableModule', 'getAttributeNames'),
		'eval'                    => array('includeBlankOption' => true, 'tl_class'=>'w50'),
	),

	'metamodel_sortby_direction' => array
		(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby_direction'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'reference'               => &$GLOBALS['TL_LANG']['tl_content'],
		'options'                 => array('ASC' => 'ASC', 'DESC' => 'DESC'),
		'eval'                    => array('includeBlankOption' => false, 'tl_class' => 'w50'),
	),

	'metamodel_filtering' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_filtering'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'options_callback'        => array('TableModule', 'getFilterSettings'),
		'default'                 => '',
		'eval' => array
		(
			'includeBlankOption'  => true,
			'submitOnChange'      => true,
			'tl_class'            => 'w50'
		),
		'wizard' => array
		(
			array('TableModule', 'editFilterSetting')
		)
	),

	'metamodel_rendersettings' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_rendersettings'],
		'exclude'                 => true,
		'inputType'               => 'select',
		'options_callback'        => array('TableModule', 'getRenderSettings'),
		'default'                 => '',
		'eval' => array
		(
			'includeBlankOption'  => true,
			'submitOnChange'      => true,
			'tl_class'            =>'w50'
		),
		'wizard' => array
		(
			array('TableModule', 'editRenderSetting')
		)
	),

	'metamodel_noparsing' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_noparsing'],
		'exclude'                 => true,
		'inputType'               => 'checkbox',
		'eval'                    => array('submitOnChange'=> true, 'tl_class' => 'clr'),
	),

	'metamodel_donotindex' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_donotindex'],
		'exclude'                 => true,
		'inputType'               => 'checkbox',
		'eval' => array
		(
			'tl_class'            => 'w50'
		),
	),

	'metamodel_filterparams' => array
		(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_filterparams'],
		'exclude'                 => true,
		'inputType'               => 'mm_subdca',
		'eval' => array
		(
			'tl_class'            => 'clr m12',
			'subfields'           => array(),
			'flagfields' => array
			(
				'use_get' => array
				(
					'label'       => &$GLOBALS['TL_LANG']['tl_module']['metamodel_filterparams_use_get'],
					'inputType'   => 'checkbox',
				),
			),
		),
	),

	'metamodel_jumpTo' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_jumpTo'],
		'exclude'                 => true,
		'exclude'                 => true,
		'inputType'               => 'pageTree',
		'eval'                    => array('fieldType'=>'radio')
	),

	'metamodel_fef_params' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_fef_params'],
		'exclude'                 => true,
		'inputType'               => 'checkboxWizard',
		'options_callback'        => array('TableModule','getFilterParameterNames'),
		'eval'                    => array('multiple'=>true, 'tl_class'=>'clr')
	),

	'metamodel_fef_autosubmit' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_fef_autosubmit'],
		'exclude'                 => true,
		'default'                 => '1',
		'inputType'               => 'checkbox'
	),

	'metamodel_fef_template' => array
	(
		'label'                   => &$GLOBALS['TL_LANG']['tl_module']['metamodel_fef_template'],
		'default'                 => 'event_full',
		'exclude'                 => true,
		'inputType'               => 'select',
		'options_callback'        => array('TableModule', 'getFilterTemplates')
	)
));