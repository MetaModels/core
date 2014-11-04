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
 * @author     Tim Becker <please.tim@metamodels.me>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_searchable_pages'] = array
(
    'config'                => array
    (
        'dataContainer'    => 'General',
        'ptable'           => 'tl_metamodel',
        'ctable'           => 'tl_metamodel_searchable_pages',
        'switchToEdit'     => false,
        'enableVersioning' => false,
    ),
    'dca_config'            => array
    (
        'data_provider'  => array
        (
            'default' => array
            (
                'source' => 'tl_metamodel_searchable_pages'
            ),
            'parent'  => array
            (
                'source' => 'tl_metamodel'
            )
        ),
        'childCondition' => array
        (
            array
            (
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_searchable_pages',
                'setOn'   => array
                (
                    array
                    (
                        'to_field'   => 'pid',
                        'from_field' => 'id',
                    ),
                ),
                'filter'  => array
                (
                    array
                    (
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                ),
                'inverse' => array
                (
                    array
                    (
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            )
        ),
    ),
    'list'                  => array
    (
        'sorting'           => array
        (
            'mode'         => 4,
            'fields'       => array('name'),
            'panelLayout'  => 'filter,limit',
            'headerFields' => array('name'),
            'flag'         => 1,
        ),
        'label'             => array
        (
            'fields' => array('name'),
            'format' => '%s',
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ),
        ),
        'operations'        => array
        (
            'edit'     => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ),
            'copy'     => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ),
            'delete'   => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ),
            'show'     => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ),
            'settings' => array
            (
                'label'   => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['settings'],
                'href'    => 'table=tl_metamodel_searchable_pages',
                'icon'    => 'system/modules/metamodels/assets/images/icons/searchable_pages_setting.png',
                'idparam' => 'pid'
            ),
        )
    ),
    'metapalettes'          => array
    (
        'default' => array
        (
            'title'   => array
            (
                'name',
                'isdefault'
            ),
            'general'    => array
            (
                'jumpTo',
                'rendersetting',
                'showEmptyValues',
            ),
        )
    ),
    'metasubselectpalettes' => array
    (
        'rendertype' => array
        (
            'standalone' => array
            (
                'backend after rendertype' => array('backendsection'),
            ),
            'ctable'     => array
            (
                'backend after rendertype' => array('ptable'),
            )
        ),
        'rendermode' => array
        (
            'flat'     => array
            (
                'display after rendermode' => array(),
            ),
            'parented' => array
            (
                'display after rendermode' => array(),
            )
        ),
    ),
    'fields'                => array
    (
        'name'            => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['name'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'mandatory' => true,
                'maxlength' => 64,
                'tl_class'  => 'w50'
            )
        ),
        'isdefault'       => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['isdefault'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'maxlength' => 255,
                'tl_class'  => 'w50 m12 cbx'
            ),
        ),

        // Maybe this is usefull?
        'showEmptyValues' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['showEmptyValues'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50'
            )
        ),
        'rendersetting'        => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['rendersetting'],
            // Todo: Catch the default rendersetting of parent MetaModel
            //'default'   => 'metamodel_prerendered',
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array
            (
                'includeBlankOption' => true,
                'tl_class'           => 'w50',
                'mandatory'          => true,
                'chosen'             => true
            )
        ),
        // Todo: Should we leave this as jumpTo or better renderPage ?
        'jumpTo'          => array
        (
            'label'          => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['jumpTo'],
            'exclude'        => true,
            'minCount'       => 1,
            'maxCount'       => 1,
            'disableSorting' => '1',
            'inputType'      => 'multiColumnWizard',
            'eval'           => array
            (
                'style'        => 'width:100%;',
                'columnFields' => array
                (
                    'langcode' => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['jumpTo_language'],
                        'exclude'   => true,
                        'inputType' => 'justtextoption',
                        'options'   => array
                        (
                            'xx' => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['jumpTo_allLanguages']
                        ),
                        'eval'      => array
                        (
                            'valign' => 'center'
                        )
                    ),
                    'value'    => array(
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['jumpTo_page'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        // TODO: change callbacks to event handlers.
                        'wizard'    => array
                        (
                            array('MetaModels\Dca\RenderSettings', 'pagePicker')
                        ),
                        'eval'      => array
                        (
                            'style' => 'width:317px;'
                        )
                    ),
                    'filter'   => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['jumpTo_filter'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'eval'      => array
                        (
                            'style'              => 'width:200px;',
                            'includeBlankOption' => true,
                            'chosen'             => true
                        )
                    ),
                ),
                'buttons'      => array
                (
                    'copy'   => false,
                    'delete' => false,
                    'up'     => false,
                    'down'   => false
                ),
                'tl_class'     => 'clr clx',
            )
        )
    )
);
