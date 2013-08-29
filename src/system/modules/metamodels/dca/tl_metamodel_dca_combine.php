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
		'closed'                                 => false,
	),

	'dca_config' => array
	(
		'data_provider'                          => array
		(
			'default'                            => array
			(
				'class'                          => 'DcGeneral\Data\TableRowsAsRecordsDriver',
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
				'tl_class'                       => 'dca_combine',
				'columnFields'                   => array
				(
                    'id'                         => array
					(
						'label'                  => null,
						'exclude'                => true,
						'inputType'              => 'justtext',
						'eval'                   => array
						(
							'hideHead'           => true,
							'hideBody'           => true,
						)
					),
					'fe_group'                   => array
					(
						'label'                  => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['fe_group'],
						'exclude'                => true,
						'inputType'              => 'select',
						'options_callback'       => array('MetaModels\Dca\DcaCombine', 'getMemberGroups'),
						'eval'                   => array
						(
							'includeBlankOption' => true,
							'style'              => 'width:115px',
							'chosen'             => 'true' // slows down the MCW like hell
						)
					),
					'be_group'                   => array
					(
						'label'                  => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['be_group'],
						'exclude'                => true,
						'inputType'              => 'select',
						'options_callback'       => array('MetaModels\Dca\DcaCombine', 'getUserGroups'),
						'eval'                   => array
						(
							'includeBlankOption' => true,
							'style'              => 'width:115px',
							'chosen'             => 'true' // slows down the MCW like hell
						)
					),
					'dca_id'                     => array
					(
						'label'                  => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_id'],
						'exclude'                => true,
						'inputType'              => 'select',
						'options_callback'       => array('MetaModels\Dca\DcaCombine', 'getModelPalettes'),
						'eval'                   => array
						(
							'style'              => 'width:180px',
							'chosen'             => 'true' // slows down the MCW like hell
						)
					),
					'view_id'                    => array
					(
						'label'                  => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['view_id'],
						'exclude'                => true,
						'inputType'              => 'select',
						'options_callback'       => array('MetaModels\Dca\DcaCombine', 'getModelViews'),
						'eval'                   => array
						(
							'style'              => 'width:180px',
							'chosen'             => 'true' // slows down the MCW like hell
						)
					),
				),
			),
			'save_callback'                      => array(array('MetaModels\Dca\DcaCombine', 'updateSort')),
		)
	)
);

