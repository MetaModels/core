<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = array('MetaModels\Dca\Module', 'buildFilterParams');

$GLOBALS['TL_DCA']['tl_module']['palettes']['metamodel_list'] =
    '{title_legend},name,headline,type;' .
    '{config_legend},metamodel,perPage,metamodel_use_limit;' .
    '{mm_filter_legend},metamodel_sortby,metamodel_sortby_direction,metamodel_sort_override,metamodel_filtering,metamodel_filterparams;' .
    '{template_legend:hide},metamodel_layout,metamodel_rendersettings,metamodel_noparsing;' .
    '{mm_meta_legend},metamodel_meta_title,metamodel_meta_description;' .
    '{protected_legend:hide},protected;' .
    '{expert_legend:hide},metamodel_donotindex,guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['metamodels_frontendfilter'] =
    '{title_legend},name,headline,type;' .
    '{mm_filter_legend},metamodel_jumpTo,metamodel,metamodel_filtering,metamodel_fef_template,metamodel_fef_params,' .
    'metamodel_fef_autosubmit,metamodel_fef_hideclearfilter,metamodel_available_values;' .
    '{protected_legend:hide},protected;' .
    '{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['metamodels_frontendclearall'] =
    '{title_legend},name,headline,type;' .
    '{mm_filter_legend},metamodel_fef_template;' .
    '{protected_legend:hide},protected;' .
    '{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'metamodel_use_limit,metamodel_sort_override';

// Insert new Subpalettes after position 1.
array_insert(
    $GLOBALS['TL_DCA']['tl_module']['subpalettes'],
    1,
    array
    (
        'metamodel_use_limit' => 'metamodel_offset,metamodel_limit',
    )
);

// Fields.
array_insert(
    $GLOBALS['TL_DCA']['tl_module']['fields'],
    1,
    array
    (
        'metamodel'                     => array
        (
            'label'      => &$GLOBALS['TL_LANG']['tl_module']['metamodel'],
            'exclude'    => true,
            'inputType'  => 'select',
            'foreignKey' => 'tl_metamodel.name',
            'eval'       => array
            (
                'mandatory'          => true,
                'chosen'             => true,
                'submitOnChange'     => true,
                'includeBlankOption' => true
            ),
            'wizard'     => array
            (
                array('MetaModels\Dca\Module', 'editMetaModel')
            )
        ),
        'metamodel_layout'              => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_module']['metamodel_layout'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('MetaModels\Dca\Module', 'getModuleTemplates'),
            'eval'             => array
            (
                'chosen'   => true,
                'tl_class' => 'w50'
            )
        ),
        'metamodel_use_limit'           => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_use_limit'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'submitOnChange' => true,
                'tl_class'       => 'w50 m12'
            ),
        ),
        'metamodel_limit'               => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_limit'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'rgxp'     => 'digit',
                'tl_class' => 'w50'
            )
        ),
        'metamodel_offset'              => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_offset'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'rgxp'     => 'digit',
                'tl_class' => 'w50'
            ),
        ),
        'metamodel_sortby'              => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('MetaModels\Dca\Module', 'getAttributeNames'),
            'eval'             => array
            (
                'includeBlankOption' => true,
                'chosen'             => true,
                'tl_class'           => 'w50'
            ),
        ),
        'metamodel_sortby_direction'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby_direction'],
            'exclude'   => true,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_content'],
            'options'   => array('ASC' => 'ASC', 'DESC' => 'DESC'),
            'eval'      => array
            (
                'includeBlankOption' => false,
                'chosen'             => true,
                'tl_class'           => 'w50'
            )
        ),
        'metamodel_sort_override'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_sort_override'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50'
            ),
        ),
        'metamodel_filtering'           => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_module']['metamodel_filtering'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('MetaModels\Dca\Module', 'getFilterSettings'),
            'default'          => '',
            'eval'             => array
            (
                'includeBlankOption' => true,
                'submitOnChange'     => true,
                'chosen'             => true,
                'tl_class'           => 'w50'
            ),
            'wizard'           => array
            (
                array('MetaModels\Dca\Module', 'editFilterSetting')
            )
        ),
        'metamodel_rendersettings'      => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_module']['metamodel_rendersettings'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('MetaModels\Dca\Module', 'getRenderSettings'),
            'default'          => '',
            'eval'             => array
            (
                'includeBlankOption' => true,
                'submitOnChange'     => true,
                'chosen'             => true,
                'tl_class'           => 'w50'
            ),
            'wizard'           => array
            (
                array('MetaModels\Dca\Module', 'editRenderSetting')
            )
        ),
        'metamodel_noparsing'           => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_noparsing'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'submitOnChange' => true,
                'tl_class'       => 'clr'
            ),
        ),
        'metamodel_donotindex'          => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_donotindex'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50'
            ),
        ),
        'metamodel_available_values'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_available_values'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50'
            ),
        ),
        'metamodel_filterparams'        => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_filterparams'],
            'exclude'   => true,
            'inputType' => 'mm_subdca',
            'eval'      => array
            (
                'tl_class'   => 'clr m12',
                'subfields'  => array(),
                'flagfields' => array
                (
                    'use_get' => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_filterparams_use_get'],
                        'inputType' => 'checkbox',
                    ),
                ),
            ),
        ),
        'metamodel_jumpTo'              => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_jumpTo'],
            'exclude'   => true,
            'inputType' => 'pageTree',
            'eval'      => array
            (
                'fieldType' => 'radio'
            )
        ),
        'metamodel_fef_params'          => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_module']['metamodel_fef_params'],
            'exclude'          => true,
            'inputType'        => 'checkboxWizard',
            'options_callback' => array('MetaModels\Dca\Module', 'getFilterParameterNames'),
            'eval'             => array
            (
                'multiple' => true,
                'tl_class' => 'clr'
            )
        ),
        'metamodel_fef_autosubmit'      => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_fef_autosubmit'],
            'exclude'   => true,
            'default'   => '1',
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50'
            ),
        ),
        'metamodel_fef_hideclearfilter' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_module']['metamodel_fef_hideclearfilter'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50'
            ),
        ),
        'metamodel_fef_template'        => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_module']['metamodel_fef_template'],
            'default'          => 'event_full',
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('MetaModels\Dca\Module', 'getFilterTemplates'),
            'eval'             => array
            (
                'tl_class' => 'w50',
                'chosen'   => true
            ),
        ),
        'metamodel_meta_title'          => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_module']['metamodel_meta_title'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('MetaModels\Dca\Module', 'getMetaTitleAttributes'),
            'eval'             => array
            (
                'tl_class'           => 'w50',
                'chosen'             => true,
                'includeBlankOption' => true
            ),
        ),
        'metamodel_meta_description'    => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_module']['metamodel_meta_description'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('MetaModels\Dca\Module', 'getMetaDescriptionAttributes'),
            'eval'             => array
            (
                'tl_class'           => 'w50',
                'chosen'             => true,
                'includeBlankOption' => true
            ),
        )
    )
);
