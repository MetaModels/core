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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tim Becker <tim@westwerk.ac>
 * @author     Alexander Menk <a.menk@imi.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_dca'] = [
    'config'                => [
        'dataContainer'    => 'General',
        'ptable'           => 'tl_metamodel',
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
            'default'                    =>
                [
                    'source' => 'tl_metamodel_dca'
                ],
            'parent'                     => [
                'source' => 'tl_metamodel'
            ],
            'tl_metamodel_dca_sortgroup' => [
                'source' => 'tl_metamodel_dca_sortgroup'
            ],
            'tl_metamodel_dcasetting'    => [
                'source' => 'tl_metamodel_dcasetting'
            ],
        ],
        'childCondition' => [
            [
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_dca',
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
                'from'   => 'tl_metamodel_dca',
                'to'     => 'tl_metamodel_dca_sortgroup',
                'setOn'  => [
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
                ],
            ],

            [
                'from'   => 'tl_metamodel_dca',
                'to'     => 'tl_metamodel_dcasetting',
                'setOn'  => [
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
            ],
        ],
    ],
    'list'                  => [
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
            'edit'               => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
            ],
            'copy'               => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg',
            ],
            'delete'             => [
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['tl_metamodel_dca']['deleteConfirm'] ?? ''
                )
            ],
            'show'               => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ],
            'groupsort_settings' => [
                'label'   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['groupsort_settings'],
                'href'    => 'table=tl_metamodel_dca_sortgroup',
                'icon'    => 'bundles/metamodelscore/images/icons/dca_groupsortsettings.png',
                'idparam' => 'pid'
            ],
            'settings'           => [
                'label'   => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['settings'],
                'href'    => 'table=tl_metamodel_dcasetting',
                'icon'    => 'bundles/metamodelscore/images/icons/dca_setting.png',
                'idparam' => 'pid'
            ],
        ]
    ],
    'metapalettes'          => [
        'default' => [
            'title'       => [
                'name'
            ],
            'view'        => [
                'panelLayout',
            ],
            'backend'     => [
                'rendertype',
                'backendcaption',
            ],
            'display'     => [
                'rendermode'
            ],
            'permissions' => [
                'iseditable',
                'iscreatable',
                'isdeleteable',
            ],
        ]
    ],
    'metasubselectpalettes' => [
        'rendertype' => [
            'standalone' => [
                'backend after rendertype' => ['backendsection'],
            ],
            'ctable'     => [
                'backend after rendertype' => ['ptable', 'backendicon'],
            ]
        ],
        'rendermode' => [
            'flat'         => [
                'display after rendermode' => ['showColumns'],
            ],
            'parented'     => [
                'display after rendermode' => ['showColumns'],
            ],
            'hierarchical' => [
                'display after rendermode' => [],
            ]
        ],
    ],
    'fields'                => [
        'id'             => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'            => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'        => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'         => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'name'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['name'],
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
        'rendertype'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertype'],
            'inputType' => 'select',
            'default'   => 'standalone',
            'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertypes'],
            'eval'      => [
                'tl_class'           => 'w50',
                'submitOnChange'     => true,
                'includeBlankOption' => true
            ],
            'sql'       => "varchar(10) NOT NULL default ''"
        ],
        'ptable'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['ptable'],
            'inputType' => 'select',
            'eval'      => [
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true
            ],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'rendermode'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendermode'],
            'inputType' => 'select',
            'default'   => 'flat',
            'eval'      => [
                'tl_class'       => 'w50',
                'submitOnChange' => true
            ],
            'sql'       => "varchar(12) NOT NULL default ''"
        ],
        'showColumns'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['showColumns'],
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50 m12 cbx'
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'backendsection' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendsection'],
            'exclude'   => true,
            'inputType' => 'select',
            'default'   => 'metamodels',
            'reference' => &$GLOBALS['TL_LANG']['MOD'],
            'eval'      =>
                [
                    'includeBlankOption' => true,
                    'valign'             => 'top',
                    'chosen'             => true,
                    'tl_class'           => 'w50'
                ],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'backendicon'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendicon'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => [
                'fieldType'  => 'radio',
                'files'      => true,
                'filesOnly'  => true,
                'extensions' => 'jpg,jpeg,gif,png,tif,tiff,svg',
                'tl_class'   => 'clr'
            ],
            'sql'       => 'blob NULL'
        ],
        'backendcaption' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendcaption'],
            'exclude'   => true,
            'inputType' => 'multiColumnWizard',
            'eval'      => [
                'tl_class'     => 'clr',
                'columnFields' => [
                    'langcode'    => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_langcode'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'options'   => $this->getLanguages(),
                        'eval'      => [
                            'tl_class' => 'clr',
                            'style'    => 'width:100%',
                            'chosen'   => 'true'
                        ]
                    ],
                    'label'       => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_label'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => [
                            'style' => 'width:100%',
                        ]
                    ],
                    'description' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_description'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => [
                            'style' => 'width:100%',
                        ]
                    ],
                ],
            ],
            'sql'       => 'text NULL'
        ],
        'panelLayout'    => [
            'label'       => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['panelLayout'],
            'exclude'     => true,
            'inputType'   => 'text',
            'default'     => 'limit',
            'eval'        =>
                [
                    'tl_class'   => 'clr w50',
                    'helpwizard' => true,
                ],
            'explanation' => 'dca_panellayout',
            'sql'         => 'blob NULL'
        ],
        'iseditable'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['iseditable'],
            'inputType' => 'checkbox',
            'default'   => 1,
            'eval'      => [
                'tl_class' => 'w50 cbx',
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'iscreatable'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['iscreatable'],
            'inputType' => 'checkbox',
            'default'   => 1,
            'eval'      => [
                'tl_class' => 'w50 cbx',
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'isdeleteable'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca']['isdeleteable'],
            'inputType' => 'checkbox',
            'default'   => 1,
            'eval'      => [
                'tl_class' => 'clr w50 cbx',
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ]
    ]
];
