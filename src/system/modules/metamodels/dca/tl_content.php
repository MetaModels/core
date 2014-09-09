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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('MetaModels\Dca\Content', 'buildCustomFilter');

$GLOBALS['TL_DCA']['tl_content']['palettes']['metamodel_content'] =
    '{title_legend},name,headline,type;' .
    '{mm_config_legend},metamodel,perPage,metamodel_use_limit;' .
    '{mm_filter_legend},metamodel_sortby,metamodel_sortby_direction,metamodel_filtering,metamodel_filterparams;' .
    '{mm_rendering},metamodel_layout,metamodel_rendersettings,metamodel_noparsing;' .
    '{mm_meta_legend},metamodel_meta_title,metamodel_meta_description;' .
    '{protected_legend:hide},protected;' .
    '{expert_legend:hide},metamodel_donotindex,guests,cssID,space';

$GLOBALS['TL_DCA']['tl_content']['palettes']['metamodels_frontendfilter'] =
    '{title_legend},name,headline,type;' .
    '{mm_filter_legend},metamodel_jumpTo,metamodel,metamodel_filtering,metamodel_fef_template,metamodel_fef_params,' .
        'metamodel_fef_autosubmit,metamodel_fef_hideclearfilter,metamodel_available_values;' .
    '{protected_legend:hide},protected;' .
    '{expert_legend:hide},guests,invisible,cssID,space';

$GLOBALS['TL_DCA']['tl_content']['palettes']['metamodels_frontendclearall'] =
    '{title_legend},name,headline,type;' .
    '{mm_filter_legend},metamodel_fef_template;' .
    '{protected_legend:hide},protected;' .
    '{expert_legend:hide},guests,invisible,cssID,space';

$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'metamodel_use_limit';

// Insert new Subpalettes after position 1.
array_insert($GLOBALS['TL_DCA']['tl_content']['subpalettes'], 1, array(
    'metamodel_use_limit' => 'metamodel_offset,metamodel_limit',
));

// Fields.
array_insert($GLOBALS['TL_DCA']['tl_content']['fields'], 1, array
(
    'metamodel' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'foreignKey'              => 'tl_metamodel.name',
        'eval' => array
        (
            'mandatory'           => true,
            'chosen'              => true,
            'submitOnChange'      => true,
            'includeBlankOption'  => true
        ),
        'wizard' => array
        (
            array('MetaModels\Dca\Content', 'editMetaModel')
        )
    ),

    'metamodel_layout' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_layout'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => array('MetaModels\Dca\Content', 'getModuleTemplates'),
        'eval'                    => array
        (
            'chosen'              => true,
            'tl_class'            => 'w50'
        )
    ),

    'metamodel_use_limit' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_use_limit'],
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'eval'                    => array('submitOnChange' => true, 'tl_class' => 'w50 m12'),
    ),

    'metamodel_limit' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_limit'],
        'exclude'                 => true,
        'inputType'               => 'text',
        'eval'                    => array('rgxp' => 'digit', 'tl_class' => 'w50')
    ),

    'metamodel_offset' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_offset'],
        'exclude'                 => true,
        'inputType'               => 'text',
        'eval'                    => array('rgxp' => 'digit', 'tl_class' => 'w50'),
    ),

    'metamodel_sortby' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_sortby'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => array('MetaModels\Dca\Content', 'getAttributeNames'),
        'eval'                    => array
        (
            'includeBlankOption'  => true,
            'chosen'              => true,
            'tl_class'            => 'w50'
        )
    ),

    'metamodel_sortby_direction' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_sortby_direction'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'reference'               => &$GLOBALS['TL_LANG']['tl_content'],
        'options'                 => array('ASC' => 'ASC', 'DESC' => 'DESC'),
        'eval'                    => array
        (
            'includeBlankOption'  => false,
            'chosen'              => true,
            'tl_class'            => 'w50'
        )
    ),

    'metamodel_filtering' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_filtering'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => array('MetaModels\Dca\Content', 'getFilterSettings'),
        'default'                 => '',
        'eval' => array
        (
            'includeBlankOption'  => true,
            'submitOnChange'      => true,
            'chosen'              => true,
            'tl_class'            => 'w50'
        ),
        'wizard' => array
        (
            array('MetaModels\Dca\Content', 'editFilterSetting')
        )
    ),

    'metamodel_rendersettings' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_rendersettings'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => array('MetaModels\Dca\Content', 'getRenderSettings'),
        'default'                 => '',
        'eval' => array
        (
            'includeBlankOption'  => true,
            'submitOnChange'      => true,
            'chosen'              => true,
            'tl_class'            => 'w50'
        ),
        'wizard' => array
        (
            array('MetaModels\Dca\Content', 'editRenderSetting')
        )
    ),

    'metamodel_noparsing' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_noparsing'],
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'eval' => array
        (
            'submitOnChange'      => true,
            'tl_class'            => 'w50'
        ),
    ),

    'metamodel_donotindex' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_donotindex'],
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'eval' => array
        (
            'tl_class'            => 'w50'
        ),
    ),

    'metamodel_available_values' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_available_values'],
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'eval' => array
        (
            'tl_class'            => 'w50'
        ),
    ),

    'metamodel_filterparams' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_filterparams'],
        'exclude'                 => true,
        'inputType'               => 'mm_subdca',
        'eval' => array
        (
            'tl_class'            => 'clr m12',
            'flagfields'          => array
            (
                'use_get'         => array
                (
                    'label'       => &$GLOBALS['TL_LANG']['tl_content']['metamodel_filterparams_use_get'],
                    'inputType'   => 'checkbox'
                ),
            ),
        )
    ),

    'metamodel_jumpTo' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_jumpTo'],
        'exclude'                 => true,
        'inputType'               => 'pageTree',
        'eval'                    => array
        (
            'fieldType'           => 'radio'
        )
    ),

    'metamodel_fef_params' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_fef_params'],
        'exclude'                 => true,
        'inputType'               => 'checkboxWizard',
        'options_callback'        => array('MetaModels\Dca\Content','getFilterParameterNames'),
        'eval'                    => array
        (
            'multiple'            => true,
            'tl_class'            => 'clr'
        )
    ),

    'metamodel_fef_autosubmit' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_fef_autosubmit'],
        'exclude'                 => true,
        'default'                 => '1',
        'inputType'               => 'checkbox',
        'eval'                    => array
        (
            'tl_class'            => 'w50'
        ),
    ),

    'metamodel_fef_hideclearfilter' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_fef_hideclearfilter'],
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'eval'                    => array
        (
            'tl_class'            => 'w50'
        ),
    ),

    'metamodel_fef_template' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_fef_template'],
        'default'                 => 'mm_filter_default',
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => array('MetaModels\Dca\Content', 'getFilterTemplates'),
        'eval'                    => array
        (
            'tl_class'            => 'w50',
            'chosen'              => true
        ),
    ),

    'metamodel_meta_title' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_meta_title'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => array('MetaModels\Dca\Content', 'getMetaTitleAttributes'),
        'eval'                    => array
        (
            'tl_class'            => 'w50',
            'chosen'              => true,
            'includeBlankOption'  => true
        ),
    ),

    'metamodel_meta_description' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_content']['metamodel_meta_description'],
        'exclude'                 => true,
        'inputType'               => 'select',
        'options_callback'        => array('MetaModels\Dca\Content', 'getMetaDescriptionAttributes'),
        'eval'                    => array
        (
            'tl_class'            => 'w50',
            'chosen'              => true,
            'includeBlankOption'  => true
        ),
    )
));
