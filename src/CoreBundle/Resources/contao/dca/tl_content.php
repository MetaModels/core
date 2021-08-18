<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use MetaModels\CoreBundle\Contao\Hooks\ContentElementCallback;

$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] =
    [ContentElementCallback::class, 'buildFilterParameterList'];

$GLOBALS['TL_DCA']['tl_content']['palettes']['metamodel_content'] =
    '{type_legend},type,headline;' .
    '{mm_config_legend},metamodel,perPage,metamodel_use_limit;' .
    '{mm_rendering_legend},metamodel_rendersettings,metamodel_layout,metamodel_noparsing,metamodel_page_param_type,metamodel_page_param,metamodel_pagination;'
    .
    '{mm_filter_legend},metamodel_filtering,metamodel_filterparams;' .
    '{mm_sorting_legend},metamodel_sortby,metamodel_sortby_direction,metamodel_sort_override;' .
    '{mm_meta_legend:hide},metamodel_meta_title,metamodel_meta_description;' .
    '{protected_legend:hide},protected;' .
    '{expert_legend:hide},guests,cssID,space;' .
    '{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['metamodels_frontendfilter'] =
    '{type_legend},type,headline;' .
    '{mm_filter_legend},metamodel,metamodel_filtering,metamodel_fef_template,metamodel_fef_params,' .
    'metamodel_fef_autosubmit,metamodel_fef_hideclearfilter,metamodel_available_values,metamodel_jumpTo;' .
    '{protected_legend:hide},protected;' .
    '{expert_legend:hide},guests,cssID,space;' .
    '{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['metamodels_frontendclearall'] =
    '{type_legend},type,headline;' .
    '{mm_filter_legend},metamodel_fef_template;' .
    '{protected_legend:hide},protected;' .
    '{expert_legend:hide},guests,cssID,space;' .
    '{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'metamodel_use_limit';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'metamodel_sort_override';

// Insert new Subpalettes after position 1.
array_insert(
    $GLOBALS['TL_DCA']['tl_content']['subpalettes'],
    1,
    [
        'metamodel_use_limit'     => 'metamodel_offset,metamodel_limit',
        'metamodel_sort_override' => 'metamodel_sort_param_type,metamodel_order_by_param,metamodel_order_dir_param'
    ]
);

// Fields.
array_insert(
    $GLOBALS['TL_DCA']['tl_content']['fields'],
    1,
    [
        'metamodel'                     => [
            'label'      => &$GLOBALS['TL_LANG']['tl_content']['metamodel'],
            'exclude'    => true,
            'inputType'  => 'select',
            'foreignKey' => 'tl_metamodel.name',
            'eval'       => [
                'mandatory'          => true,
                'chosen'             => true,
                'submitOnChange'     => true,
                'includeBlankOption' => true,
                'tl_class'           => 'w50'
            ],
            'wizard'     => [
                [ContentElementCallback::class, 'editMetaModelButton']
            ],
            'sql'        => "int(10) unsigned NOT NULL default '0'"
        ],
        'metamodel_layout'              => [
            'label'            => &$GLOBALS['TL_LANG']['tl_content']['metamodel_layout'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [ContentElementCallback::class, 'getTemplates'],
            'eval'             => [
                'chosen'   => true,
                'tl_class' => 'clr w50'
            ],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'metamodel_noparsing'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_noparsing'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50 m12 cbx'
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'metamodel_page_param_type'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_page_param_type'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['slugNget', 'slug', 'get'],
            'reference' => &$GLOBALS['TL_LANG']['tl_content']['metamodel_param_type_options'],
            'default'   => 'slugNget',
            'eval'      => [
                'tl_class' => 'clr w50'
            ],
            'sql'       => "varchar(64) NOT NULL default 'slugNget'"
        ],
        'metamodel_page_param'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_page_param'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'tl_class' => 'w50',
                'rgxp'     => 'alias'
            ],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'metamodel_pagination'          => [
            'label'            => &$GLOBALS['TL_LANG']['tl_content']['metamodel_pagination'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [ContentElementCallback::class, 'getPaginationTemplates'],
            'eval'             => [
                'chosen'   => true,
                'tl_class' => 'clr w50'
            ],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'metamodel_use_limit'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_use_limit'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'submitOnChange' => true,
                'tl_class'       => 'w50 m12'
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'metamodel_limit'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_limit'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'rgxp'     => 'digit',
                'tl_class' => 'w50'
            ],
            'sql'       => "smallint(5) NOT NULL default '0'"
        ],
        'metamodel_offset'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_offset'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'rgxp'     => 'digit',
                'tl_class' => 'w50'
            ],
            'sql'       => "smallint(5) NOT NULL default '0'"
        ],
        'metamodel_sortby'              => [
            'label'            => &$GLOBALS['TL_LANG']['tl_content']['metamodel_sortby'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [ContentElementCallback::class, 'getAttributeNames'],
            'eval'             => [
                'includeBlankOption' => true,
                'chosen'             => true,
                'tl_class'           => 'clr w50'
            ],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'metamodel_sortby_direction'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_sortby_direction'],
            'exclude'   => true,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_content'],
            'options'   => ['asc' => 'ASC', 'desc' => 'DESC'],
            'eval'      => [
                'includeBlankOption' => false,
                'chosen'             => true,
                'tl_class'           => 'w50'
            ],
            'sql'       => "varchar(4) NOT NULL default ''"
        ],
        'metamodel_sort_override'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_sort_override'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'submitOnChange' => true,
                'tl_class'       => 'clr w50 m12 cbx'
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'metamodel_sort_param_type'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_sort_param_type'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['slug', 'get', 'slugNget'],
            'reference' => &$GLOBALS['TL_LANG']['tl_content']['metamodel_param_type_options'],
            'default'   => 'slug',
            'eval'      => [
                'tl_class' => 'w50'
            ],
            'sql'       => "varchar(64) NOT NULL default 'slug'"
        ],
        'metamodel_order_by_param'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_order_by_param'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'tl_class' => 'clr w50',
                'rgxp'     => 'alias'
            ],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'metamodel_order_dir_param'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_order_dir_param'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'tl_class' => 'w50',
                'rgxp'     => 'alias'
            ],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'metamodel_filtering'           =>
            [
                'label'            => &$GLOBALS['TL_LANG']['tl_content']['metamodel_filtering'],
                'exclude'          => true,
                'inputType'        => 'select',
                'options_callback' => [ContentElementCallback::class, 'getFilterSettings'],
                'default'          => '0',
                'eval'             => [
                    'includeBlankOption' => true,
                    'submitOnChange'     => true,
                    'chosen'             => true,
                    'tl_class'           => 'clr w50'
                ],
                'wizard'           => [
                    [ContentElementCallback::class, 'editFilterSettingButton']
                ],
                'sql'              => "int(10) NOT NULL default '0'"
            ],
        'metamodel_rendersettings'      => [
            'label'            => &$GLOBALS['TL_LANG']['tl_content']['metamodel_rendersettings'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [ContentElementCallback::class, 'getRenderSettings'],
            'default'          => '0',
            'eval'             => [
                'includeBlankOption' => true,
                'submitOnChange'     => true,
                'chosen'             => true,
                'tl_class'           => 'w50'
            ],
            'wizard'           => [
                [ContentElementCallback::class, 'editRenderSettingButton']
            ],
            'sql'              => "int(10) NOT NULL default '0'"
        ],
        'metamodel_donotindex'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_donotindex'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50'
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'metamodel_available_values'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_available_values'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50'
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'metamodel_filterparams'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_filterparams'],
            'exclude'   => true,
            'inputType' => 'mm_subdca',
            'eval'      => [
                'tl_class'   => 'clr',
                'subfields'  => [],
                'flagfields' => [
                    'use_get' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_filterparams_use_get'],
                        'inputType' => 'checkbox'
                    ],
                ],
            ],
            'sql'       => 'longblob NULL'
        ],
        'metamodel_jumpTo'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_jumpTo'],
            'exclude'   => true,
            'inputType' => 'pageTree',
            'eval'      => [
                'fieldType' => 'radio',
                'tl_class'  => 'clr'
            ],
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ],
        'metamodel_fef_params'          => [
            'label'            => &$GLOBALS['TL_LANG']['tl_content']['metamodel_fef_params'],
            'exclude'          => true,
            'inputType'        => 'checkboxWizard',
            'options_callback' => [ContentElementCallback::class, 'getFilterParameterNames'],
            'eval'             => [
                'multiple' => true,
                'tl_class' => 'clr w50'
            ],
            'sql'              => 'blob NULL'
        ],
        'metamodel_fef_autosubmit'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_fef_autosubmit'],
            'exclude'   => true,
            'default'   => '1',
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'clr w50'
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'metamodel_fef_hideclearfilter' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_content']['metamodel_fef_hideclearfilter'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50'
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'metamodel_fef_template'        => [
            'label'            => &$GLOBALS['TL_LANG']['tl_content']['metamodel_fef_template'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [ContentElementCallback::class, 'getFilterTemplates'],
            'eval'             => [
                'tl_class' => 'w50',
                'chosen'   => true
            ],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'metamodel_meta_title'          => [
            'label'            => &$GLOBALS['TL_LANG']['tl_content']['metamodel_meta_title'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [ContentElementCallback::class, 'getMetaTitleAttributes'],
            'eval'             => [
                'tl_class'           => 'w50',
                'chosen'             => true,
                'includeBlankOption' => true
            ],
            'sql'              => "varchar(64) NOT NULL default ''"
        ],
        'metamodel_meta_description'    => [
            'label'            => &$GLOBALS['TL_LANG']['tl_content']['metamodel_meta_description'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [ContentElementCallback::class, 'getMetaDescriptionAttributes'],
            'eval'             => [
                'tl_class'           => 'w50',
                'chosen'             => true,
                'includeBlankOption' => true
            ],
            'sql'              => "varchar(64) NOT NULL default ''"
        ]
    ]
);

$GLOBALS['TL_DCA']['tl_content']['fields']['perPage']['eval']['tl_class'] = 'clr w50';
