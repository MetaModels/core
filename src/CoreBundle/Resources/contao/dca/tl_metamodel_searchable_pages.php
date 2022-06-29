<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
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
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_searchable_pages'] = [
    'config'       => [
        'dataContainer'    => 'General',
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
            'parent' => [
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
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['deleteConfirm'] ?? ''
                )
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ],
            'toggle' => [
                'label'          => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['toggle'],
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
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'           =>
            [
                'sql' => "int(10) unsigned NOT NULL default '0'"
            ],
        'tstamp'        => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'name'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['name'],
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
        'filter'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['filter'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'includeBlankOption' => true,
                'chosen'             => true,
                'submitOnChange'     => true,
                'tl_class'           => 'clr w50',
            ],
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ],
        'filterparams'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['filterparams'],
            'exclude'   => true,
            'inputType' => 'mm_subdca',
            'eval'      => [
                'tl_class'   => 'clr m12',
                'flagfields' => [
                    'use_get' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['filterparams'],
                        'inputType' => 'checkbox'
                    ],
                ],
            ],
            'sql'       => 'longblob NULL'
        ],
        'rendersetting' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['rendersetting'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'includeBlankOption' => true,
                'mandatory'          => true,
                'chosen'             => true,
                'tl_class'           => 'w50',
            ],
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ],
        'published' => [
            'default' => 1,
            'sql'     => "char(1) NOT NULL default '1'"
        ]
    ]
];
