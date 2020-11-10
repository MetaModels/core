<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
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
        'sql'              => array
        (
            'keys' => array
            (
                'id'  => 'primary',
                'pid' => 'index'
            ),
        ),
    ),
    'dca_config'            => array
    (
        'data_provider'  => array
        (
            'default'      => array
            (
                'source' => 'tl_metamodel_dca_sortgroup'
            ),
            'parent'       => array
            (
                'source' => 'tl_metamodel_dca'
            ),
            'tl_metamodel' => array
            (
                'source' => 'tl_metamodel'
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
            ),
            array
            (
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_dca',
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
            'fields'       => array('sorting'),
            'panelLayout'  => 'limit',
            'headerFields' => array('name'),
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
                'icon'  => 'edit.svg',
            ),
            'copy'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg',
            ),
            'delete' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ),
            'show'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
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
                'display after rendergroupattr' => array
                (
                    'rendergrouplen'
                ),
            )
        ),
        'ismanualsort'    => array
        (
            '!1' => array
            (
                'display after ismanualsort' => array
                (
                    'rendersortattr',
                    'rendersort',
                    'rendergrouptype',
                ),
            )
        )
    ),
    'fields'                => array
    (
        'id'              => array
        (
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ),
        'pid'             => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting'         => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp'          => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
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
            ),
            'sql'       => 'text NULL'
        ),
        'isdefault'       => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['isdefault'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50 m12 cbx',
                'fallback' => true
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'ismanualsort'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['ismanualsort'],
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class'       => 'w50 m12 cbx',
                'submitOnChange' => true
            ),
            'sql'       => "char(1) NOT NULL default ''"
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
            'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersortdirections'],
            'sql'       => "varchar(10) NOT NULL default 'asc'"
        ),
        'rendersortattr'  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersortattr'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array
            (
                'tl_class' => 'w50 clr',
            ),
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ),
        'rendergrouptype' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptype'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array('none', 'char', 'digit', 'day', 'weekday', 'week', 'month', 'year'),
            'default'   => 'none',
            'eval'      => array
            (
                'tl_class'       => 'w50 clr',
                'submitOnChange' => true
            ),
            'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes'],
            'sql'       => "varchar(10) NOT NULL default 'none'"
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
            'sql'       => "int(10) unsigned NOT NULL default '0'"
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
            'sql'       => "int(10) unsigned NOT NULL default '1'"
        )
    )
);
