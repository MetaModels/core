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

$GLOBALS['TL_DCA']['tl_metamodel_dca_combine'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'                          => 'General',
		'ptable'                                 => 'tl_metamodel',
		'switchToEdit'                           => false,
		'enableVersioning'                       => false,
		'closed'                                 => true,
	),

	'dca_config' => array
	(
		'data_provider'                          => array
		(
			'default'                            => array
			(
				'class'                          => 'GeneralDataTableRowsAsRecords',
				'source'                         => 'tl_metamodel_dca_combine',
				'group_column'                   => 'pid',
				'sort_column'                    => 'sorting'
			)
		),
	),

	// Palettes
	'palettes' => array
	(
		'default' => 'rows'
	),

	// Fields
	'fields' => array
	(
		'rows' => array
		(
			'label'                              => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_combiner'],
			'exclude'                            => true,
			'inputType'                          => 'multiColumnWizard',
			'eval'                               => array
			(
				'explanation'                    => 'customsql',
				'class'                          => 'clr',
				'columnFields'                   => array
				(
					'id'                         => array
					(
						'label'                  => null,
						'exclude'                => true,
						'inputType'              => 'justtext',
						'eval'                   => array
						(
							'columnPos'          => 1,
							'style'              => 'width:0',
							// 'chosen'          => 'true' // slows down the MCW like hell
						)
					),
					'fe_group'                   => array
					(
						'label'                  => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['fe_group'],
						'exclude'                => true,
						'inputType'              => 'select',
						'options_callback'       => array('MetaModelDcaCombiner', 'getMemberGroups'),
						'eval'                   => array
						(
							'includeBlankOption' => true,
							'style'              => 'width:100px',
							// 'chosen'          => 'true' // slows down the MCW like hell
						)
					),
					'be_group'                   => array
					(
						'label'                  => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['be_group'],
						'exclude'                => true,
						'inputType'              => 'select',
						'options_callback'       => array('MetaModelDcaCombiner', 'getUserGroups'),
						'eval'                   => array
						(
							'includeBlankOption' => true,
							'style'              => 'width:100px',
							// 'chosen'=> 'true' // slows down the MCW like hell
						)
					),
					'dca_id'                     => array
					(
						'label'                  => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_id'],
						'exclude'                => true,
						'inputType'              => 'select',
						'options_callback'       => array('MetaModelDcaCombiner', 'getModelPalettes'),
						'eval'                   => array
						(
							'style'              => 'width:180px',
							// 'chosen'          => 'true' // slows down the MCW like hell
						)
					),
					'view_id'                    => array
					(
						'label'                  => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['view_id'],
						'exclude'                => true,
						'inputType'              => 'select',
						'options_callback'       => array('MetaModelDcaCombiner', 'getModelViews'),
						'eval'                   => array
						(
							'style'              => 'width:180px',
							// 'chosen'          => 'true' // slows down the MCW like hell
						)
					),
				),
			),
			'save_callback'                      => array(array('MetaModelDcaCombiner', 'updateSort')),
		)
	)
);



?>