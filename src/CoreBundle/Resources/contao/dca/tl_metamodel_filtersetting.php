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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Table tl_metamodel_attribute
 */

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting'] = [
    'config'                => [
        'dataContainer'    => 'General',
        'label'            => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['list_label'],
        'switchToEdit'     => false,
        'enableVersioning' => false,
        'sql'              => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index'
            ],
        ]
    ],
    'dca_config'            => [
        'data_provider'  => [
            'root'   => [
                'source' => 'tl_metamodel_filtersetting'
            ],
            'parent' => [
                'source' => 'tl_metamodel_filter',
            ],
        ],
        'childCondition' => [
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
            [
                'from'   => 'tl_metamodel_filtersetting',
                'to'     => 'tl_metamodel_filtersetting',
                'setOn'  => [
                    [
                        'to_field'   => 'pid',
                        'from_field' => 'id',
                    ],
                    [
                        'to_field'   => 'fid',
                        'from_field' => 'fid',
                    ],
                ],
                'filter' => [
                    [
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ]
            ]
        ],
        'rootEntries'    => [
            'tl_metamodel_filtersetting' => [
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
            'tl_metamodel_filtersetting' => [
                'fields' => [
                    'type',
                    'attr_id',
                    'urlparam',
                    'comment',
                    'enabled'
                ],
                'format' => '%s %s',
            ],
        ]
    ],
    'list'                  => [
        'sorting'           => [
            'mode'         => 5,
            'fields'       => ['sorting'],
            'headerFields' => ['type', 'attr_id'],
            'flag'         => 1,
            'icon'         => 'bundles/metamodelscore/images/icons/filter_and.png',
        ],
        'label'             => [
            'fields' => [
                'fid',
                'type',
                'attr_id',
                'urlparam',
                'comment'
            ],
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
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg'
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg'
            ],
            'cut'    => [
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['cut'],
                'href'       => 'act=paste&amp;mode=cut',
                'icon'       => 'cut.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ],
            'toggle' => [
                'label'          => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['toggle'],
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
        'default'                          => [
            'title' => [
                'type',
                'enabled',
                'comment'
            ],
        ],
        '_attribute_ extends default'      => [
            'config' => [
                'attr_id'
            ]
        ],
        'conditionor extends default'      => [
            'config' => [
                'stop_after_match'
            ]
        ],
        'idlist extends default'           => [
            '+config' => [
                'items'
            ],
        ],
        'simplelookup extends _attribute_' => [
            '+fefilter' => [
                'urlparam',
                'predef_param',
                'fe_widget',
                'allow_empty',
                'label',
                'template',
                'defaultid',
                'blankoption',
                'onlyused',
                'onlypossible',
                'skipfilteroptions',
                'hide_label'
            ],
        ],
        'customsql extends default'        => [
            '+config' => [
                'customsql'
            ],
        ]
    ],
    'metasubselectpalettes' => [
        'attr_id' => []
    ],
    'simplelookup_palettes' => [
        '_translated_' => [
            'config' => [
                'all_langs'
            ]
        ]
    ],
    'fields'                => [
        'id'                => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'               => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'           => [
            'sorting' => true,
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'            => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'fid'               => [
            // Keep this empty but keep it here!
            // needed for act=copy in DC_Table, as otherwise the fid value will not be copied.
            'label' => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['fid'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'type'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['type'],
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
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'enabled'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['enabled'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => 1,
            'eval'      => [
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12 cbx',
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'comment'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['comment'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'clr long'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'attr_id'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['attr_id'],
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
        'all_langs'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['all_langs'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12 cbx',
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'items'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['items'],
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => [
                'doNotSaveEmpty' => true,
                'alwaysSave'     => true,
                'mandatory'      => true,
            ],
            'sql'       => 'text NULL'
        ],
        'urlparam'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['urlparam'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'tl_class' => 'w50',
            ],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'predef_param'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['predef_param'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12 cbx',
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'fe_widget'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['fe_widget'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12 cbx',
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'customsql'         => [
            'label'       => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['customsql'],
            'exclude'     => true,
            'inputType'   => 'textarea',
            'default'     => 'SELECT id FROM {{table}}
WHERE 1 = 1',
            'eval'        => [
                'allowHtml'      => true,
                'preserveTags'   => true,
                'decodeEntities' => true,
                'rte'            => 'ace|sql',
                'class'          => 'monospace',
                'helpwizard'     => true,
            ],
            'explanation' => 'customsql',
            'sql'         => 'text NULL'
        ],
        'allow_empty'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['allow_empty'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12 cbx',
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'stop_after_match'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['stop_after_match'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'alwaysSave' => true,
                'tl_class'   => 'w50 cbx',
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'label'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['label'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'tl_class' => 'clr w50',
            ],
            'sql'       => 'blob NULL'
        ],
        'template'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['template'],
            'default'   => 'mm_filteritem_default',
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'tl_class' => 'w50',
                'chosen'   => true
            ],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'blankoption'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['blankoption'],
            'exclude'   => true,
            'default'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'clr w50 m12 cbx',
            ],
            'sql'       => "char(1) NOT NULL default '1'"
        ],
        'onlyused'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['onlyused'],
            'exclude'   => true,
            'default'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class'       => 'w50 m12 cbx',
                'submitOnChange' => true,
            ],
            'sql'       => "char(1) NOT NULL default '0'"
        ],
        'onlypossible'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['onlypossible'],
            'exclude'   => true,
            'default'   => true,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50 m12 cbx',
            ],
            'sql'       => "char(1) NOT NULL default '0'"
        ],
        'skipfilteroptions' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['skipfilteroptions'],
            'exclude'   => true,
            'default'   => false,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50 cbx',
            ],
            'sql'       => "char(1) NOT NULL default '0'"
        ],
        'defaultid'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['defaultid'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'tl_class'           => 'clr w50',
                'includeBlankOption' => true
            ],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'hide_label' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['hide_label'],
            'exclude'   => true,
            'default'   => false,
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50 m12 cbx',
            ],
            'sql'       => "char(1) NOT NULL default '0'"
        ]
    ]
];
