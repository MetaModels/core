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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_dca_sortgroup'] = array
(
    'config'                => array
    (
        'dataContainer'    => 'General',
        'ptable'           => 'tl_metamodel_dca',
        'switchToEdit'     => false,
        'enableVersioning' => false,
    ),
    'dca_config'            => array
    (
        'data_provider'  => array
        (
            'default' => array
            (
                'source' => 'tl_metamodel_dca_sortgroup'
            ),
            'parent'  => array
            (
                'source' => 'tl_metamodel_dca'
            )
        ),
        'childCondition' => array
        (
            array
            (
                'from'    => 'tl_metamodel_dca',
                'to'      => 'tl_metamodel_dca_sortgroup',
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
            'panelLayout'  => 'limit',
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
            'edit'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ),
            'copy'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ),
            'delete' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ),
            'show'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
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
            'display' => array
            (
                'ismanualsort',
            ),
        )
    ),
    'metasubselectpalettes' => array
    (
        'rendertype'      => array
        (
            'standalone' => array
            (
                'backend after rendertype' => array
                (
                    'backendsection'
                ),
            ),
            'ctable'     => array
            (
                'backend after rendertype' => array
                (
                    'ptable'
                ),
            )
        ),
        'rendergrouptype' => array
        (
            '!none' => array
            (
                'display after rendergrouptype' => array
                (
                    'rendergroupattr'
                ),
            ),
            'char'  => array
            (
                'display after rendergrouptype' => array
                (
                    'rendergrouplen'
                ),
            )
        ),
        'ismanualsort'    => array
        (
            '!1' => array
            (
                'display after rendergrouplen' => array
                (
                    'rendergrouptype',
                    'rendersortattr',
                    'rendersort',
                ),
            )
        )
    ),
    'fields'                => array
    (
        'name'            => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['name'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['isdefault'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'maxlength' => 255,
                'tl_class'  => 'w50 m12 cbx'
            ),
        ),
        'ismanualsort'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['ismanualsort'],
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class'       => 'w50 m12 cbx',
                'submitOnChange' => true
            )
        ),
        'rendersort'      => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersort'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array('asc', 'desc'),
            'eval'      => array
            (
                'tl_class' => 'w50',
            ),
            'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersortdirections']
        ),
        'rendersortattr'  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersortattr'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array
            (
                'tl_class' => 'w50',
            ),
        ),
        'rendergrouptype' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptype'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array('none', 'char', 'digit', 'day', 'weekday', 'week', 'month', 'year'),
            'eval'      => array
            (
                'tl_class'       => 'w50',
                'submitOnChange' => true
            ),
            'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']
        ),
        'rendergroupattr' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergroupattr'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array
            (
                'tl_class'       => 'w50',
                'submitOnChange' => true
            ),
        ),
        'rendergrouplen'  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouplen'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'tl_class' => 'w50',
                'rgxp'     => 'digit'
            ),
        ),
        'backendcaption'  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['backendcaption'],
            'exclude'   => true,
            'inputType' => 'multiColumnWizard',
            'eval'      => array
            (
                'columnFields' => array
                (
                    'langcode'    => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_langcode'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'options'   => $this->getLanguages(),
                        'eval'      => array
                        (
                            'style'  => 'width:200px',
                            'chosen' => 'true'
                        )
                    ),
                    'label'       => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_label'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => array
                        (
                            'style' => 'width:180px',
                        )
                    ),
                    'description' => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_description'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => array
                        (
                            'style' => 'width:200px',
                        )
                    ),
                ),
            )
        ),
    )
);
