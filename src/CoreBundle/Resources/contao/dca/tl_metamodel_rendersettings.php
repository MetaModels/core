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
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Alexander Menk <a.menk@imi.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2012-2024 The MetaModels team.
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
            'copy'     => [
                'label'       => 'copy.label',
                'description' => 'copy.description',
                'href'        => 'act=copy',
                'icon'        => 'copy.svg'
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
            'settings' =>
                [
                    'label'       => 'settings.label',
                    'description' => 'settings.description',
                    'href'        => 'table=tl_metamodel_rendersetting',
                    'icon'        => 'bundles/metamodelscore/images/icons/rendersetting.png',
                    'idparam'     => 'pid'
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
            'label' => 'id.label',
            'sql'   => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'             => [
            'label' => 'pid.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'          => [
            'label' => 'tstamp.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'name'            => [
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
        'hideEmptyValues' => [
            'label'       => 'hideEmptyValues.label',
            'description' => 'hideEmptyValues.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 cbx'
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'hideLabels'      => [
            'label'       => 'hideLabels.label',
            'description' => 'hideLabels.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 cbx'
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'template'        => [
            'label'       => 'template.label',
            'description' => 'template.description',
            'default'     => 'metamodel_prerendered',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'includeBlankOption' => true,
                'tl_class'           => 'w50',
                'mandatory'          => true,
                'chosen'             => true
            ],
            'sql'         => "varchar(64) NOT NULL default ''"
        ],
        'format'          => [
            'label'       => 'format.label',
            'description' => 'format.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'options'     => [
                'html5',
                'text'
            ],
            'reference'   => [
                'html5' => 'formatOptions.html5',
                'text'  => 'formatOptions.text',
            ],
            'eval'        => [
                'includeBlankOption' => true,
                'tl_class'           => 'w50',
                'chosen'             => true
            ],
            'sql'         => "varchar(255) NOT NULL default ''"
        ],
        'jumpTo'          => [
            'label'          => 'jumpTo.label',
            'description'    => 'jumpTo.description',
            'exclude'        => true,
            'minCount'       => 1,
            'maxCount'       => 1,
            'disableSorting' => '1',
            'inputType'      => 'multiColumnWizard',
            'eval'           => [
                'useTranslator' => true,
                'dragAndDrop'   => false,
                'hideButtons'   => true,
                'style'         => 'width:100%;',
                'tl_class'      => 'clr clx',
                'columnFields'  => [
                    'langcode' => [
                        'label'       => 'jumpTo_language.label',
                        'description' => 'jumpTo_language.description',
                        'exclude'     => true,
                        'inputType'   => 'justtextoption',
                        'eval'        => [
                            'valign' => 'center'
                        ]
                    ],
                    'value'    => [
                        'label'       => 'jumpTo_page.label',
                        'description' => 'jumpTo_page.description',
                        'exclude'     => true,
                        'inputType'   => 'text',
                        'eval'        => [
                            'style' => 'width:90%;'
                        ]
                    ],
                    'filter'   => [
                        'label'       => 'jumpTo_filter.label',
                        'description' => 'jumpTo_filter.description',
                        'exclude'     => true,
                        'inputType'   => 'select',
                        'eval'        => [
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
            'label'       => 'additionalCss.label',
            'description' => 'additionalCss.description',
            'exclude'     => true,
            'inputType'   => 'multiColumnWizard',
            'eval'        => [
                'useTranslator' => true,
                'style'         => 'width:100%;',
                'tl_class'      => 'w50',
                'columnFields'  => [
                    'file'      => [
                        'label'       => 'file.label',
                        'description' => 'file.description',
                        'exclude'     => true,
                        'inputType'   => 'select',
                        'eval'        => [
                            'style'              => 'width:100%;',
                            'chosen'             => true,
                            'includeBlankOption' => true
                        ]
                    ],
                    'published' => [
                        'label'       => 'publish.label',
                        'description' => 'publish.description',
                        'exclude'     => true,
                        'inputType'   => 'checkbox',
                        'eval'        => [
                            'style' => 'width:40px;'
                        ]
                    ],
                ]
            ],
            'sql'         => 'blob NULL'
        ],
        'additionalJs'    => [
            'label'       => 'additionalJs.label',
            'description' => 'additionalJs.description',
            'exclude'     => true,
            'inputType'   => 'multiColumnWizard',
            'eval'        => [
                'useTranslator' => true,
                'style'         => 'width:100%;',
                'tl_class'      => 'w50',
                'columnFields'  => [
                    'file'      => [
                        'label'       => 'file.label',
                        'description' => 'file.description',
                        'exclude'     => true,
                        'inputType'   => 'select',
                        'eval'        => [
                            'style'              => 'width:100%;',
                            'chosen'             => true,
                            'includeBlankOption' => true
                        ]
                    ],
                    'published' => [
                        'label'       => 'publish.label',
                        'description' => 'publish.description',
                        'exclude'     => true,
                        'inputType'   => 'checkbox',
                        'eval'        => [
                            'style' => 'width:40px;'
                        ]
                    ],
                ]
            ],
            'sql'         => 'blob NULL'
        ],
    ],
];
