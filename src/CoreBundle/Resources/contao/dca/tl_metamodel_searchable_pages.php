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
 * @author     Tim Becker <please.tim@metamodel.me>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\DC\General;

$GLOBALS['TL_DCA']['tl_metamodel_searchable_pages'] = [
    'config'       => [
        'dataContainer'    => General::class,
        'ptable'           => 'tl_metamodel',
        'switchToEdit'     => false,
        'enableVersioning' => false,
        'sql'              => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index',
            ],
        ]
    ],
    'dca_config'   => [
        'data_provider'  => [
            'default' => [
                'source' => 'tl_metamodel_searchable_pages'
            ],
            'parent'  => [
                'source' => 'tl_metamodel'
            ]
        ],
        'childCondition' => [
            [
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_searchable_pages',
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
            ]
        ],
    ],
    'list'         => [
        'sorting'           => [
            'mode'         => 4,
            'fields'       => ['name'],
            'panelLayout'  => 'filter,limit',
            'headerFields' => ['name'],
            'flag'         => 1,
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
    'metapalettes' => [
        'default' => [
            'title'   => [
                'name',
            ],
            'general' => [
                'rendersetting',
                'filter',
                'filterparams',
            ],
        ]
    ],
    'fields'       => [
        'id'            => [
            'label' => 'id.label',
            'sql'   => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'           => [
            'label' => 'pid.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'        => [
            'label' => 'tstamp.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'name'          => [
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
        'filter'        => [
            'label'       => 'filter.label',
            'description' => 'filter.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'includeBlankOption' => true,
                'chosen'             => true,
                'submitOnChange'     => true,
                'tl_class'           => 'clr w50',
            ],
            'sql'         => "int(10) unsigned NOT NULL default '0'"
        ],
        'filterparams'  => [
            'label'       => 'filterparams.label',
            'description' => 'filterparams.description',
            'exclude'     => true,
            'inputType'   => 'mm_subdca',
            'eval'        => [
                'tl_class'   => 'clr m12',
                'flagfields' => [
                    'use_get' => [
                        'label'       => 'filterparams.label',
                        'description' => 'filterparams.description',
                        'inputType'   => 'checkbox'
                    ],
                ],
            ],
            'sql'         => 'longblob NULL'
        ],
        'rendersetting' => [
            'label'       => 'rendersetting.label',
            'description' => 'rendersetting.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'includeBlankOption' => true,
                'mandatory'          => true,
                'chosen'             => true,
                'tl_class'           => 'w50',
            ],
            'sql'         => "int(10) unsigned NOT NULL default '0'"
        ],
        'published'     => [
            'label'       => 'published.label',
            'description' => 'published.description',
            'default'     => 1,
            'sql'         => "char(1) NOT NULL default '1'"
        ]
    ]
];
