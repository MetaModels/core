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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

/**
 * Table tl_metamodel_attribute
 */

$GLOBALS['TL_DCA']['tl_metamodel_filter'] = array
(
    'config' => array
    (
        'dataContainer'    => 'General',
        'switchToEdit'     => false,
        'enableVersioning' => false,
    ),

    'dca_config' => array
    (
        'data_provider'  => array
        (
            'parent'                     => array
            (
                'source' => 'tl_metamodel'
            ),
            'tl_metamodel_filtersetting' => array
            (
                'source' => 'tl_metamodel_filtersetting'
            ),
        ),
        'childCondition' => array
        (
            array
            (
                'from'   => 'tl_metamodel',
                'to'     => 'tl_metamodel_filter',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'pid',
                        'from_field' => 'id',
                    ),
                ),
                'filter' => array
                (
                    array
                    (
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                ),
            ),

            array(
                'from'   => 'tl_metamodel_filter',
                'to'     => 'tl_metamodel_filtersetting',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'fid',
                        'from_field' => 'id',
                    )
                ),
                'filter' => array
                (
                    array
                    (
                        'local'     => 'fid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            ),
        ),
    ),

    'list' => array
    (
        'sorting' => array
        (
            'mode'         => 4,
            'fields'       => array
            (
                'name'
            ),
            'panelLayout'  => 'filter,sort,limit',
            'headerFields' => array
            (
                'name'
            ),
            'flag'         => 1,
        ),

        'label' => array
        (
            'fields' => array
            (
                'name'
            ),
            'format' => '%s'
        ),

        'global_operations' => array
        (
            'all' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            )
        ),

        'operations' => array
        (
            'edit'     => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_filter']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ),
            'delete'   => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_filter']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ),
            'show'     => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_filter']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ),
            'settings' => array
            (
                'label'   => &$GLOBALS['TL_LANG']['tl_metamodel_filter']['settings'],
                'href'    => 'table=tl_metamodel_filtersetting',
                'idparam' => 'pid',
                'icon'    => 'system/modules/metamodels/assets/images/icons/filter_setting.png',
            ),
        )
    ),

    'metapalettes' => array
    (
        'default' => array
        (
            'title' => array
            (
                'name'
            )
        ),
    ),

    'fields' => array
    (
        'tstamp' => array
        (),
        'name'   => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filter']['name'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class'  => 'w50'
            )
        ),
    )
);
