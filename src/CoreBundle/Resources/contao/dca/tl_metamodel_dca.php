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
 * @author     Tim Becker <tim@westwerk.ac>
 * @author     Alexander Menk <a.menk@imi.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use Contao\System;

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
                'label'       => 'all.label',
                'description' => 'all.description',
                'href'        => 'act=select',
                'class'       => 'header_edit_all',
                'attributes'  => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations'        => [
            'edit'               => [
                'label'       => 'edit.label',
                'description' => 'edit.description',
                'href'        => 'act=edit',
                'icon'        => 'edit.svg',
            ],
            'copy'               => [
                'label'       => 'copy.label',
                'description' => 'copy.description',
                'href'        => 'act=copy',
                'icon'        => 'copy.svg',
            ],
            'delete'             => [
                'label'       => 'delete.label',
                'description' => 'delete.description',
                'href'        => 'act=delete',
                'icon'        => 'delete.svg',
                'attributes'  => 'onclick="if (!confirm(this.dataset.msgConfirm)) return false; Backend.getScrollOffset();"',
            ],
            'show'               => [
                'label'       => 'show.label',
                'description' => 'show.description',
                'href'        => 'act=show',
                'icon'        => 'show.svg'
            ],
            'groupsort_settings' => [
                'label'       => 'groupsort_settings.label',
                'description' => 'groupsort_settings.description',
                'href'        => 'table=tl_metamodel_dca_sortgroup',
                'icon'        => 'bundles/metamodelscore/images/icons/dca_groupsortsettings.png',
                'idparam'     => 'pid'
            ],
            'settings'           => [
                'label'       => 'settings.label',
                'description' => 'settings.description',
                'href'        => 'table=tl_metamodel_dcasetting',
                'icon'        => 'bundles/metamodelscore/images/icons/dca_setting.png',
                'idparam'     => 'pid'
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
                'rendermode',
                'subheadline'
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
            'label' => 'id.label',
            'sql'   => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'            => [
            'label' => 'pid.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'        => [
            'label' => 'sorting.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'         => [
            'label' => 'tstamp.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'name'           => [
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
        'rendertype'     => [
            'label'       => 'rendertype.label',
            'description' => 'rendertype.description',
            'inputType'   => 'select',
            'default'     => 'standalone',
            'reference'   => [
                'standalone' => 'rendertypes.standalone',
                'ctable'     => 'rendertypes.ctable',
            ],
            'eval'        => [
                'tl_class'           => 'w50',
                'submitOnChange'     => true,
                'includeBlankOption' => true
            ],
            'sql'         => "varchar(10) NOT NULL default ''"
        ],
        'ptable'         => [
            'label'       => 'ptable.label',
            'description' => 'ptable.description',
            'inputType'   => 'select',
            'eval'        => [
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true
            ],
            'sql'         => "varchar(64) NOT NULL default ''"
        ],
        'rendermode'     => [
            'label'       => 'rendermode.label',
            'description' => 'rendermode.description',
            'inputType'   => 'select',
            'default'     => 'flat',
            'eval'        => [
                'tl_class'       => 'w50',
                'submitOnChange' => true
            ],
            'sql'         => "varchar(12) NOT NULL default ''"
        ],
        'showColumns'    => [
            'label'       => 'showColumns.label',
            'description' => 'showColumns.description',
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 m12 cbx'
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'backendsection' => [
            'label'       => 'backendsection.label',
            'description' => 'backendsection.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'default'     => 'metamodels',
            'eval'        =>
                [
                    'includeBlankOption' => true,
                    'valign'             => 'top',
                    'chosen'             => true,
                    'tl_class'           => 'w50'
                ],
            'sql'         => "varchar(255) NOT NULL default ''"
        ],
        'backendicon'    => [
            'label'       => 'backendicon.label',
            'description' => 'backendicon.description',
            'exclude'     => true,
            'inputType'   => 'fileTree',
            'eval'        => [
                'fieldType'  => 'radio',
                'files'      => true,
                'filesOnly'  => true,
                'extensions' => 'jpg,jpeg,gif,png,tif,tiff,svg',
                'tl_class'   => 'clr'
            ],
            'sql'         => 'blob NULL'
        ],
        'backendcaption' => [
            'label'       => 'backendcaption.label',
            'description' => 'backendcaption.description',
            'exclude'     => true,
            'inputType'   => 'multiColumnWizard',
            'eval'        => [
                'useTranslator' => true,
                'tl_class'      => 'clr',
                'columnFields'  => [
                    'langcode'    => [
                        'label'       => 'becap_langcode.label',
                        'description' => 'becap_langcode.description',
                        'exclude'     => true,
                        'inputType'   => 'select',
                        'options'     => static fn () => System::getContainer()->get('contao.intl.locales')->getLocales(),
                        'eval'        => [
                            'tl_class' => '',
                            'style'    => 'width:400px',
                            'chosen'   => 'true'
                        ]
                    ],
                    'label'       => [
                        'label'       => 'becap_label.label',
                        'description' => 'becap_label.description',
                        'exclude'     => true,
                        'inputType'   => 'text',
                        'eval'        => [
                            'style' => 'width:100%',
                        ]
                    ],
                    'description' => [
                        'label'       => 'becap_description.label',
                        'description' => 'becap_description.description',
                        'exclude'     => true,
                        'inputType'   => 'text',
                        'eval'        => [
                            'style' => 'width:100%',
                        ]
                    ],
                ],
            ],
            'sql'         => 'text NULL'
        ],
        'panelLayout'    => [
            'label'       => 'panelLayout.label',
            'description' => 'panelLayout.description',
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
            'label'       => 'iseditable.label',
            'description' => 'iseditable.description',
            'inputType'   => 'checkbox',
            'default'     => 1,
            'eval'        => [
                'tl_class' => 'w50 cbx',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'iscreatable'    => [
            'label'       => 'iscreatable.label',
            'description' => 'iscreatable.description',
            'inputType'   => 'checkbox',
            'default'     => 1,
            'eval'        => [
                'tl_class' => 'w50 cbx',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'isdeleteable'   => [
            'label'       => 'isdeleteable.label',
            'description' => 'isdeleteable.description',
            'inputType'   => 'checkbox',
            'default'     => 1,
            'eval'        => [
                'tl_class' => 'clr w50 cbx',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'subheadline'    => [
            'label'       => 'subheadline.label',
            'description' => 'subheadline.description',
            'inputType'   => 'text',
            'eval'        => [
                'maxlength' => 255,
                'tl_class'  => 'w50'
            ],
            'sql'         => "varchar(255) NOT NULL default ''"
        ]
    ]
];
