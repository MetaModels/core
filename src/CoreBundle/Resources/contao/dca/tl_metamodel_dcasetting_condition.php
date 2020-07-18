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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Table tl_metamodel_dcasetting_condition
 */

$GLOBALS['TL_DCA']['tl_metamodel_dcasetting_condition'] = [
    'config'                => [
        'dataContainer'    => 'General',
        'label'            => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['list_label'],
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
            'root'   => [
                'source' => 'tl_metamodel_dcasetting_condition'
            ],
            'parent' => [
                'source' => 'tl_metamodel_dcasetting',
            ],
            'tl_metamodel_dcasetting' => [
                'source' => 'tl_metamodel_dcasetting'
            ]
        ],
        'childCondition' => [
            [
                'from'   => 'tl_metamodel_dcasetting',
                'to'     => 'tl_metamodel_dcasetting_condition',
                'setOn'  => [
                    [
                        'to_field'   => 'settingId',
                        'from_field' => 'id',
                    ]
                ],
                'filter' => [
                    [
                        'local'     => 'settingId',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ],
                'inverse' => [
                    [
                        'local'     => 'settingId',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ]
            ],
            [
                'from'   => 'tl_metamodel_dcasetting_condition',
                'to'     => 'tl_metamodel_dcasetting_condition',
                'setOn'  => [
                    [
                        'to_field'   => 'pid',
                        'from_field' => 'id',
                    ],
                    [
                        'to_field'   => 'settingId',
                        'from_field' => 'settingId',
                    ],
                ],
                'filter' => [
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
        'rootEntries'    => [
            'tl_metamodel_dcasetting_condition' => [
                'setOn'  => [
                    [
                        'property' => 'pid',
                        'value'    => '0'
                    ],
                ],
                'filter' => [
                    [
                        'property'  => 'pid',
                        'operation' => '=',
                        'value'     => '0'
                    ]
                ]
            ]
        ],
        'child_list'     => [
            'tl_metamodel_dcasetting_condition' => [
                'fields' => [
                    'condition',
                    'attr_id',
                    'comment',
                    'enabled'
                ],
                'format' => '%s %s',
            ],
        ]
    ],
    'list'                  => [
        'sorting'           =>
            [
                'mode'         => 5,
                'fields'       => ['sorting'],
                'headerFields' => [
                    'type',
                    'attr_id'
                ],
                'flag'         => 1,
                'icon'         => 'bundles/metamodelscore/images/icons/filter_and.png',
            ],
        'label'             => [
            'fields' => [
                'type',
                'attr_id',
                'comment'
            ],
            'format' => '%s %s %s',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ]
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg'
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg'
            ],
            'cut'    => [
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['cut'],
                'href'       => 'act=paste&amp;mode=cut',
                'icon'       => 'cut.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ],
            'toggle' => [
                'label'          => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['toggle'],
                'icon'           => 'visible.svg',
                'toggleProperty' => 'enabled',
            ]
        ]
    ],
    'palettes'              => [
        '__selector__' => [
            'type'
        ]
    ],
    'metapalettes'          => [
        'default'                                           => [
            'basic' => [
                'type',
                'enabled',
                'comment'
            ],
        ],
        '_attribute_ extends default'                       => [
            '+config' => [
                'attr_id'
            ]
        ],
        'conditionor extends default'                       => [],
        'conditionand extends default'                      => [],
        'conditionpropertyvalueis extends _attribute_'      => [
            '+config' => [
                'value'
            ]
        ],
        'conditionpropertycontainanyof extends _attribute_' => [
            '+config' => [
                'value'
            ]
        ],
        'conditionpropertyvisible extends _attribute_'      => [],
    ],
    'metasubselectpalettes' => [
        'attr_id' => []
    ],
    'fields'                => [
        'id'        => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'       => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'   => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'settingId' => [
            // Keep this empty but keep it here!
            // needed for act=copy in DC_Table, as otherwise the fid value will not be copied.
            'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['fid'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'type'      => [
            'label'       => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['type'],
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'doNotSaveEmpty'     => true,
                'alwaysSave'         => true,
                'submitOnChange'     => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'chosen'             => true,
                'helpwizard'         => true
            ],
            'explanation' => 'dcasetting_condition',
            'sql'         => "varchar(255) NOT NULL default ''"
        ],
        'enabled'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['enabled'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => 1,
            'eval'      => [
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12 cbx',
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'comment'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['comment'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'tl_class' => 'clr long'
            ],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'attr_id'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['attr_id'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'doNotSaveEmpty'     => true,
                'alwaysSave'         => true,
                'submitOnChange'     => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'chosen'             => true
            ],
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ],
        'value'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['value'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'alwaysSave'         => true,
                'includeBlankOption' => true,
                'tl_class'           => 'w50',
                'chosen'             => true
            ],
            'sql'       => 'blob NULL'
        ],
    ]
];
