<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
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
 * @copyright  2012-2024 The MetaModels team.
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
                'from'   => 'tl_metamodel',
                'to'     => 'tl_metamodel_dca',
                'setOn'  =>
                    [
                        [
                            'to_field'   => 'pid',
                            'from_field' => 'id',
                        ],
                    ],
                'filter' => [
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
                'label'       => 'all.label',
                'description' => 'all.description',
                'href'        => 'act=select',
                'class'       => 'header_edit_all',
                'attributes'  => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label'       => 'edit.label',
                'description' => 'edit.description',
                'href'        => 'act=edit',
                'icon'        => 'edit.svg',
            ],
            'copy'   => [
                'label'       => 'copy.label',
                'description' => 'copy.description',
                'href'        => 'act=copy',
                'icon'        => 'copy.svg',
            ],
            'delete' => [
                'label'       => 'delete.label',
                'description' => 'delete.description',
                'href'        => 'act=delete',
                'icon'        => 'delete.svg',
                'attributes'  => 'onclick="if (!confirm(this.dataset.msgConfirm)) return false; Backend.getScrollOffset();"',
            ],
            'show'   => [
                'label'       => 'show.label',
                'description' => 'show.description',
                'href'        => 'act=show',
                'icon'        => 'show.svg'
            ],
            'toggle' => [
                'label'          => 'toggle.label',
                'description'    => 'toggle.description',
                'icon'           => 'visible.svg',
                'toggleProperty' => 'published'
            ]
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
            'label' => 'id.label',
            'sql'   => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'             => [
            'label' => 'pid.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'         => [
            'label' => 'sorting.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'          => [
            'label' => 'tstamp.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'published'       => [
            'label'       => 'published.label',
            'default' => 1,
            'sql'     => "char(1) NOT NULL default '1'"
        ],
        'name'            => [
            'label'       => 'name.label',
            'description' => 'name.description',
            'exclude'     => true,
            'search'      => true,
            'inputType'   => 'text',
            'eval'        => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class'  => 'w50'
            ],
            'sql'         => "varchar(255) NOT NULL default ''"
        ],
        'isdefault'       => [
            'label'       => 'isdefault.label',
            'description' => 'isdefault.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 m12 cbx',
                'fallback' => true
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'ismanualsort'    => [
            'label'       => 'ismanualsort.label',
            'description' => 'ismanualsort.description',
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class'       => 'w50 cbx',
                'submitOnChange' => true
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'rendersort'      => [
            'label'       => 'rendersort.label',
            'description' => 'rendersort.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'options'     => ['asc', 'desc'],
            'eval'        => [
                'tl_class' => 'w50',
            ],
            'reference'   => [
                'asc'  => 'rendersortdirections.asc',
                'desc' => 'rendersortdirections.desc',
            ],
            'sql'         => "varchar(10) NOT NULL default 'asc'"
        ],
        'rendersortattr'  => [
            'label'       => 'rendersortattr.label',
            'description' => 'rendersortattr.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'tl_class' => 'w50 clr',
                'chosen'   => true
            ],
            'sql'         => "int(10) unsigned NOT NULL default '0'"
        ],
        'rendergrouptype' => [
            'label'       => 'rendergrouptype.label',
            'description' => 'rendergrouptype.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'options'     => ['none', 'char', 'digit', 'day', 'weekday', 'week', 'month', 'year'],
            'default'     => 'none',
            'eval'        => [
                'tl_class'       => 'w50 clr',
                'submitOnChange' => true
            ],
            'reference'   => [
                'none'    => 'rendergrouptypes.none',
                'char'    => 'rendergrouptypes.char',
                'digit'   => 'rendergrouptypes.digit',
                'day'     => 'rendergrouptypes.day',
                'weekday' => 'rendergrouptypes.weekday',
                'week'    => 'rendergrouptypes.week',
                'month'   => 'rendergrouptypes.month',
                'year'    => 'rendergrouptypes.year',
            ],
            'sql'         => "varchar(10) NOT NULL default 'none'"
        ],
        'rendergroupattr' => [
            'label'       => 'rendergroupattr.label',
            'description' => 'rendergroupattr.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'tl_class' => 'w50',
                'chosen'   => true
            ],
            'sql'         => "int(10) unsigned NOT NULL default '0'"
        ],
        'rendergrouplen'  => [
            'label'       => 'rendergrouplen.label',
            'description' => 'rendergrouplen.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'eval'        => [
                'tl_class' => 'w50',
                'rgxp'     => 'digit'
            ],
            'sql'         => "int(10) unsigned NOT NULL default '1'"
        ]
    ]
];
