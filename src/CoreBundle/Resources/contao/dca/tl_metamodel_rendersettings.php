<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
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
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Alexander Menk <a.menk@imi.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\DC\General;

$GLOBALS['TL_DCA']['tl_metamodel_rendersettings'] = [
    'config'       => [
        'dataContainer'    => General::class,
        'ptable'           => 'tl_metamodel',
        'switchToEdit'     => false,
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
            'default'                    => [
                'source' => 'tl_metamodel_rendersettings'
            ],
            'parent'                     => [
                'source' => 'tl_metamodel'
            ],
            'tl_metamodel_rendersetting' => [
                'source' => 'tl_metamodel_rendersetting'
            ],
        ],
        'childCondition' => [
            [
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_rendersettings',
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
                'from'   => 'tl_metamodel_rendersettings',
                'to'     => 'tl_metamodel_rendersetting',
                'setOn'  => [
                    [
                        'to_field'   => 'pid',
                        'from_field' => 'id',
                    ],
                ],
                'filter' =>
                    [
                        [
                            'local'     => 'pid',
                            'remote'    => 'id',
                            'operation' => '=',
                        ],
                    ]
            ],
        ],
        'child_list'     => [
            'tl_metamodel_rendersettings' => [
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
            ]
        ],
        'operations'        => [
            'edit'     => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg'
            ],
            'copy'     => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg'
            ],
            'delete'   => [
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['deleteConfirm'] ?? ''
                )
            ],
            'show'     => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ],
            'settings' =>
                [
                    'label'   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['settings'],
                    'href'    => 'table=tl_metamodel_rendersetting',
                    'icon'    => 'bundles/metamodelscore/images/icons/rendersetting.png',
                    'idparam' => 'pid'
                ],
        ]
    ],
    'metapalettes' => [
        'default' => [
            'title'      => [
                'name'
            ],
            'general'    => [
                'template',
                'format'
            ],
            'expert'     => [
                'hideEmptyValues',
                'hideLabels',
            ],
            'jumpto'     => [
                'jumpTo'
            ],
            'additional' => [
                ':hide',
                'additionalCss',
                'additionalJs'
            ]
        ],
    ],
    'fields'       => [
        'id'              => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'             => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'          => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'name'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['name'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class'  => 'w50'
            ],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'hideEmptyValues' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['hideEmptyValues'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50 cbx'
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'hideLabels'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['hideLabels'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50 cbx'
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'template'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['template'],
            'default'   => 'metamodel_prerendered',
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'includeBlankOption' => true,
                'tl_class'           => 'w50',
                'mandatory'          => true,
                'chosen'             => true
            ],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'format'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['format'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => [
                'html5',
                'text'
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['formatOptions'],
            'eval'      => [
                'includeBlankOption' => true,
                'tl_class'           => 'w50',
                'chosen'             => true
            ],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'jumpTo'          => [
            'label'          => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo'],
            'exclude'        => true,
            'minCount'       => 1,
            'maxCount'       => 1,
            'disableSorting' => '1',
            'inputType'      => 'multiColumnWizard',
            'eval'           => [
                'dragAndDrop'  => false,
                'hideButtons'  => true,
                'style'        => 'width:100%;',
                'tl_class'     => 'clr clx',
                'columnFields' => [
                    'langcode' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo_language'],
                        'exclude'   => true,
                        'inputType' => 'justtextoption',
                        'eval'      => [
                            'valign' => 'center'
                        ]
                    ],
                    'value'    => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo_page'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => [
                            'style' => 'width:90%;'
                        ]
                    ],
                    'filter'   => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo_filter'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'eval'      => [
                            'style'              => 'width:100%;',
                            'includeBlankOption' => true,
                            'chosen'             => true
                        ]
                    ],
                ],
            ],
            'sql'            => 'blob NULL'
        ],
        'additionalCss'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['additionalCss'],
            'exclude'   => true,
            'inputType' => 'multiColumnWizard',
            'eval'      => [
                'style'        => 'width:100%;',
                'tl_class'     => 'w50',
                'columnFields' => [
                    'file'      => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['file'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'eval'      => [
                            'style'              => 'width:100%;',
                            'chosen'             => true,
                            'includeBlankOption' => true
                        ]
                    ],
                    'published' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['publish'],
                        'exclude'   => true,
                        'inputType' => 'checkbox',
                        'eval'      => [
                            'style' => 'width:40px;'
                        ]
                    ],
                ]
            ],
            'sql'       => 'blob NULL'
        ],
        'additionalJs'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['additionalJs'],
            'exclude'   => true,
            'inputType' => 'multiColumnWizard',
            'eval'      => [
                'style'        => 'width:100%;',
                'tl_class'     => 'w50',
                'columnFields' => [
                    'file'      => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['file'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'eval'      => [
                            'style'              => 'width:100%;',
                            'chosen'             => true,
                            'includeBlankOption' => true
                        ]
                    ],
                    'published' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['publish'],
                        'exclude'   => true,
                        'inputType' => 'checkbox',
                        'eval'      => [
                            'style' => 'width:40px;'
                        ]
                    ],
                ]
            ],
            'sql'       => 'blob NULL'
        ],
    ],
];
