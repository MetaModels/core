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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\DC\General;

$GLOBALS['TL_DCA']['tl_metamodel_filter'] = [
    'config'       => [
        'dataContainer'    => General::class,
        'switchToEdit'     => false,
        'enableVersioning' => false,
        'sql'              => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index'
            ],
        ],
    ],
    'dca_config'   => [
        'data_provider'  => [
            'default' => [
                'source' => 'tl_metamodel_filter'
            ],
            'parent'  => [
                'source' => 'tl_metamodel'
            ],

            'tl_metamodel_filtersetting' => [
                'source' => 'tl_metamodel_filtersetting'
            ],
        ],
        'childCondition' => [
            [
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_filter',
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
                'from'   => 'tl_metamodel_filter',
                'to'     => 'tl_metamodel_filtersetting',
                'setOn'  => [
                    [
                        'to_field'   => 'fid',
                        'from_field' => 'id',
                    ]
                ],
                'filter' => [
                    [
                        'local'     => 'fid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ]
            ],
        ],
    ],
    'list'         => [
        'sorting'           => [
            'mode'         => 4,
            'fields'       => [
                'name'
            ],
            'panelLayout'  => 'filter,sort,limit',
            'headerFields' => [
                'name'
            ],
            'flag'         => 1,
        ],
        'label'             => [
            'fields' => [
                'name'
            ],
            'format' => '%s'
        ],
        'global_operations' => [
            'all' => [
                'label'       => 'all.label',
                'description' => 'all.description',
                'href'        => 'act=select',
                'class'       => 'header_edit_all',
                'attributes'  => 'onclick="Backend.getScrollOffset();"'
            ]
        ],
        'operations'        => [
            'edit'     => [
                'label'       => 'edit.label',
                'description' => 'edit.description',
                'href'        => 'act=edit',
                'icon'        => 'edit.svg'
            ],
            'delete'   => [
                'label'       => 'delete.label',
                'description' => 'delete.description',
                'href'        => 'act=delete',
                'icon'        => 'delete.svg',
                'attributes'  => 'onclick="if (!confirm(this.dataset.msgConfirm)) return false; Backend.getScrollOffset();"',
            ],
            'show'     => [
                'label'       => 'show.label',
                'description' => 'show.description',
                'href'        => 'act=show',
                'icon'        => 'show.svg'
            ],
            'settings' => [
                'label'       => 'settings.label',
                'description' => 'settings.description',
                'href'        => 'table=tl_metamodel_filtersetting',
                'idparam'     => 'pid',
                'icon'        => 'bundles/metamodelscore/images/icons/filter_setting.png',
            ],
        ]
    ],
    'metapalettes' => [
        'default' => [
            'title' => [
                'name'
            ]
        ],
    ],
    'fields'       => [
        'id'     => [
            'label' => 'id.label',
            'sql'   => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'    => [
            'label' => 'pid.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp' => [
            'label' => 'tstamp.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'name'   => [
            'label'       => 'name.label',
            'description' => 'name.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'eval'        => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class'  => 'w50'
            ],
            'sql'         => "varchar(255) NOT NULL default ''"
        ],
    ]
];
