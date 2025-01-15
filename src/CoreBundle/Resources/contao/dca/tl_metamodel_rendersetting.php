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
                'label'       => 'addall.label',
                'description' => 'addall.description',
                'class'       => 'header_add_all rendersetting_add_all',
                'attributes'  => 'onclick="Backend.getScrollOffset();"'
            ],
            'all'    => [
                'label'       => 'all.label',
                'description' => 'all.description',
                'href'        => 'act=select',
                'class'       => 'header_edit_all',
                'attributes'  => 'onclick="Backend.getScrollOffset();"'
            ]
        ],
        'operations'        => [
            'edit'   => [
                'label'       => 'edit.label',
                'description' => 'edit.description',
                'href'        => 'act=edit',
                'icon'        => 'edit.svg'
            ],
            'cut'    => [
                'label'       => 'cut.label',
                'description' => 'cut.description',
                'icon'        => 'cut.svg'
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
            'label' => 'id.label',
            'sql'   => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'              => [
            'label' => 'pid.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'          => [
            'label' => 'sorting.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'           => [
            'label' => 'tstamp.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'attr_id'          => [
            'label'       => 'attr_id.label',
            'description' => 'attr_id.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'doNotSaveEmpty'     => true,
                'alwaysSave'         => true,
                'submitOnChange'     => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
                'chosen'             => true,
                'tl_class'           => 'w50'
            ],
            'sql'         => "int(10) unsigned NOT NULL default '0'"
        ],
        'template'         => [
            'label'       => 'template.label',
            'description' => 'template.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'tl_class'           => 'w50',
                'chosen'             => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
            ],
            'sql'         => "varchar(64) NOT NULL default ''"
        ],
        'additional_class' => [
            'label'       => 'additional_class.label',
            'description' => 'additional_class.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'eval'        => [
                'tl_class'  => 'w50',
                'maxlength' => 64,
            ],
            'sql'         => "varchar(64) NOT NULL default ''"
        ],
        'enabled'          => [
            'label'       => 'enabled.label',
            'description' => 'enabled.description',
            'default'     => 1,
            'sql'         => "char(1) NOT NULL default ''"
        ]
    ]
];
