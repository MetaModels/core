<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

$this->loadLanguageFile('languages');

$GLOBALS['TL_DCA']['tl_metamodel'] = array
(
	'config'                          => array
	(
		'dataContainer'               => 'General',

		'ctable'                      => array
		(
			'tl_metamodel_attribute',
			'tl_metamodel_filter',
			'tl_metamodel_rendersettings',
			'tl_metamodel_dca',
			'tl_metamodel_dca_combine'
		),
		'switchToEdit'                => true,
		'enableVersioning'            => true,
	),

	'list'                            => array
	(
		'sorting'                     => array
		(
			'mode'                    => 2,
			'fields'                  => array(),
			'flag'                    => 1,
			'panelLayout'             => 'sort,limit'
		),

		'label'                       => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s',
		),

		'global_operations'           => array
		(
			'all'                     => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
		),

		'operations'                  => array
		(
			'edit'                    => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif',
			),

			'cut'                     => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif'
			),

			'delete'                  => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => sprintf(
					'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
					$GLOBALS['TL_LANG']['MSC']['deleteConfirm']
				)
			),

			'show'                    => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),

			'fields'                  => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['fields'],
				'href'                => 'table=tl_metamodel_attribute',
				'icon'                => 'system/modules/metamodels/assets/images/icons/fields.png',
				'idparam'             => 'pid'
			),

			'rendersettings'          => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['rendersettings'],
				'href'                => 'table=tl_metamodel_rendersettings',
				'icon'                => 'system/modules/metamodels/assets/images/icons/rendersettings.png',
				'idparam'             => 'pid'
			),

			'dca'                     => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['dca'],
				'href'                => 'table=tl_metamodel_dca',
				'icon'                => 'system/modules/metamodels/assets/images/icons/dca.png',
				'idparam'             => 'pid'
			),

			'filter'                  => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['filter'],
				'href'                => 'table=tl_metamodel_filter',
				'icon'                => 'system/modules/metamodels/assets/images/icons/filter.png',
				'idparam'             => 'pid'
			),

			'dca_combine'             => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel']['dca_combine'],
				'href'                => 'table=tl_metamodel_dca_combine&act=edit',
				'icon'                => 'system/modules/metamodels/assets/images/icons/dca_combine.png',
				'idparam'             => 'metamodel'
			),
		)
	),

	'metapalettes'                    => array
	(
		'default'                     => array
		(
			'title'                   => array
			(
				'name',
				'tableName'
			),

			'translated'              => array
			(
				':hide',
				'translated'
			),

			'advanced'                => array
			(
				':hide',
				'varsupport'
			),
		)
	),

	'metasubpalettes'                 => array
	(
		'translated'                  => array
		(
			'languages'
		),
	),

	'fields'                          => array
	(
		'tstamp'                      => array
		(
		),

		'sorting'                     => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['sorting'],
			'sorting'                 => true,
			'flag'                    => 11
		),

		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['name'],
			'sorting'                 => true,
			'flag'                    => 3,
			'length'                  => 1,
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => true,
				'maxlength'           => 64,
				'tl_class'            => 'w50',
				'unique'              => true
			)
		),

		'tableName'                   => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['tableName'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array
			(
				'mandatory'           => true,
				'maxlength'           => 64,
				'doNotCopy'           => true,
				'tl_class'            => 'w50'
			),
		),

		'translated'                  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['translated'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'clr',
				'submitOnChange'      => true
			)
		),

		'languages'                   => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['languages'],
			'exclude'                 => true,
			'inputType'               => 'multiColumnWizard',
			'eval'                    => array
			(
				'columnFields'        => array
				(
					'langcode'        => array
					(
						'label'       => &$GLOBALS['TL_LANG']['tl_metamodel']['languages_langcode'],
						'exclude'     => true,
						'inputType'   => 'select',
						'options'     => $this->getLanguages(),
						'eval'        => array
						(
							'style'   => 'width:470px',
							'chosen'  => 'true'
						)
					),
					'isfallback'      => array
					(
						'label'       => &$GLOBALS['TL_LANG']['tl_metamodel']['languages_isfallback'],
						'exclude'     => true,
						'inputType'   => 'checkbox',
						'eval'        => array
						(
							'style'   => 'width:50px',
						)
					),
				),
			),
		),

		'varsupport'                  => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel']['varsupport'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array
			(
				'tl_class'            => 'clr',
				'submitOnChange'      => true
			)
		),
	)
);
