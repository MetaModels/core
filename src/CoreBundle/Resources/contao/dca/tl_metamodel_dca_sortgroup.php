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

$GLOBALS['TL_DCA']['tl_metamodel_dca_sortgroup'] = [
    'config'                => [
        'dataContainer'    => 'General',
        'ptable'           => 'tl_metamodel_dca',
        'switchToEdit'     => false,
        'enableVersioning' => false,
        'sql'              => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index'
            ],
        ],
    ],
    'dca_config'            => [
        'data_provider'  => [
            'default'      => [
                'source' => 'tl_metamodel_dca_sortgroup'
            ],
            'parent'       => [
                'source' => 'tl_metamodel_dca'
            ],
            'tl_metamodel' => [
                'source' => 'tl_metamodel'
            ]
        ],
        'childCondition' => [
            [
                'from'    => 'tl_metamodel_dca',
                'to'      => 'tl_metamodel_dca_sortgroup',
                'setOn'   => [
                    [
                        'to_field'   => 'pid',
                        'from_field' => 'id',
                    ],
                ],
                'filter'  => [
                    [
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ],
                'inverse' => [
                    [
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ]
            ],
            [
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_dca',
                'setOn'   =>
                    [
                        [
                            'to_field'   => 'pid',
                            'from_field' => 'id',
                        ],
                    ],
                'filter'  => [
                    [
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ]
            ]
        ],
    ],
    'list'                  => [
        'sorting'           => [
            'mode'         => 4,
            'fields'       => ['sorting'],
            'panelLayout'  => 'limit',
            'headerFields' => ['name'],
        ],
        'label'             => [
            'fields' => ['name'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ],
        ]
    ],
    'metapalettes'          => [
        'default' => [
            'title'   => [
                'name',
                'isdefault'
            ],
            'display' => [
                'ismanualsort',
            ],
        ]
    ],
    'metasubselectpalettes' => [
        'rendergrouptype' => [
            '!none' => [
                'display after rendergrouptype' => [
                    'rendergroupattr'
                ],
            ],
            'char'  => [
                'display after rendergroupattr' => [
                    'rendergrouplen'
                ],
            ]
        ],
        'ismanualsort'    => [
            '!1' => [
                'display after ismanualsort' => [
                    'rendersortattr',
                    'rendersort',
                    'rendergrouptype',
                ],
            ]
        ]
    ],
    'fields'                => [
        'id'              => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'             => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'         =>
            [
                'sql' => "int(10) unsigned NOT NULL default '0'"
            ],
        'tstamp'          => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'name'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['name'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class'  => 'w50'
            ],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'isdefault'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['isdefault'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50 m12 cbx',
                'fallback' => true
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'ismanualsort'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['ismanualsort'],
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class'       => 'w50 cbx',
                'submitOnChange' => true
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'rendersort'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersort'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['asc', 'desc'],
            'eval'      => [
                'tl_class' => 'w50',
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersortdirections'],
            'sql'       => "varchar(10) NOT NULL default 'asc'"
        ],
        'rendersortattr'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersortattr'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'tl_class' => 'w50 clr',
            ],
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ],
        'rendergrouptype' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptype'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['none', 'char', 'digit', 'day', 'weekday', 'week', 'month', 'year'],
            'default'   => 'none',
            'eval'      => [
                'tl_class'       => 'w50 clr',
                'submitOnChange' => true
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes'],
            'sql'       => "varchar(10) NOT NULL default 'none'"
        ],
        'rendergroupattr' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergroupattr'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'tl_class'       => 'w50',
                'submitOnChange' => true
            ],
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ],
        'rendergrouplen'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouplen'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'tl_class' => 'w50',
                'rgxp'     => 'digit'
            ],
            'sql'       => "int(10) unsigned NOT NULL default '1'"
        ]
    ]
];
