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
 * @author     Christopher Boelter <c.boelter@cogizz.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_rendersetting'] = [
    'config'       => [
        'dataContainer'    => 'General',
        'ptable'           => 'tl_metamodel_rendersettings',
        'switchToEdit'     => true,
        'enableVersioning' => false,
        'sql'              => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index'
            ],
        ]
    ],
    'dca_config'   => [
        'data_provider'  => [
            'default'      => [
                'source' => 'tl_metamodel_rendersetting'
            ],
            'parent'       => [
                'source' => 'tl_metamodel_rendersettings'
            ],
            'tl_metamodel' => [
                'source' => 'tl_metamodel'
            ]
        ],
        'childCondition' => [
            [
                'from'    => 'tl_metamodel_rendersettings',
                'to'      => 'tl_metamodel_rendersetting',
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
                'to'      => 'tl_metamodel_rendersettings',
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
        'child_list'     => [
            'tl_metamodel_rendersetting' => [
                'fields' => [
                    'type',
                    'attr_id',
                    'urlparam',
                    'comment'
                ],
                'format' => '%s %s',
            ],
        ],
    ],
    'list'         => [
        'sorting'           => [
            'mode'         => 4,
            'fields'       => ['sorting'],
            'panelLayout'  => 'limit',
            'headerFields' => ['name'],
        ],
        'global_operations' => [
            'addall' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addall'],
                'class'      => 'header_add_all rendersetting_add_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
            'all'    => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ]
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg'
            ],
            'cut'    => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['cut'],
                'icon'  => 'cut.svg'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['deleteConfirm'] ?? ''
                )
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ],
            'toggle' => [
                'label'          => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['toggle'],
                'icon'           => 'visible.svg',
                'toggleProperty' => 'enabled',
            ]
        ]
    ],
    'palettes'     => [
        '__selector__' => [
            'attr_id'
        ]
    ],
    'metapalettes' => [
        'default' => [
            'title' => [
                'attr_id',
                'template',
                'additional_class'
            ]
        ],
    ],
    // Fields.
    'fields'       => [
        'id'               => [
            'label' => 'id.0',
            'sql'   => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'              => [
            'label' => 'pid.0',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'          => [
            'label' => 'sorting.0',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'           => [
            'label' => 'tstamp.0',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'attr_id'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['attr_id'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'doNotSaveEmpty'     => true,
                'alwaysSave'         => true,
                'submitOnChange'     => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
                'chosen'             => true,
                'tl_class'           => 'w50'
            ],
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ],
        'template'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['template'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'tl_class'           => 'w50',
                'chosen'             => true,
                'includeBlankOption' => true,
            ],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'additional_class' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['additional_class'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'tl_class'  => 'w50',
                'maxlength' => 64,
            ],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'enabled'          => [
            'default' => 1,
            'sql'     => "char(1) NOT NULL default ''"
        ]
    ]
];
