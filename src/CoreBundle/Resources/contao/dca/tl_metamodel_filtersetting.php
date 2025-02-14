<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
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
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

use ContaoCommunityAlliance\DcGeneral\DC\General;

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting'] = [
    'config'                => [
        'dataContainer'    => General::class,
        'label'            => 'list_label.label',
        'description'      => 'list_label.description',
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
                'source' => 'tl_metamodel_filtersetting',
            ],
            'parent' => [
                'source' => 'tl_metamodel_filter',
            ],
        ],
        'childCondition' => [
            [
                'from'    => 'tl_metamodel_filter',
                'to'      => 'tl_metamodel_filtersetting',
                'setOn'   => [
                    [
                        'to_field'   => 'fid',
                        'from_field' => 'id',
                    ]
                ],
                'filter'  => [
                    [
                        'local'     => 'fid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ],
                'inverse' => [
                    [
                        'local'     => 'fid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ],
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
            'deepcopy' => [
                'label'       => 'copy.label',
                'description' => 'copy.description',
                'href'        => 'act=deepcopy',
                'icon'        => 'copychilds.svg'
            ],
            'cut'      => [
                'label'       => 'cut.label',
                'description' => 'cut.description',
                'icon'        => 'cut.svg',
                'attributes'  => 'onclick="Backend.getScrollOffset()"',
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
            'toggle'   => [
                'label'          => 'toggle.label',
                'description'    => 'toggle.description',
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
                'hide_label',
                'label_as_blankoption',
                'template',
                'defaultid',
                'apply_sorting',
                'blankoption',
                'onlyused',
                'onlypossible',
                'skipfilteroptions',
                'cssID'
            ],
        ],
        'customsql extends default'        => [
            '+config' => [
                'customsql',
                'use_only_in_env'
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
        'id'                   => [
            'label' => 'id.label',
            'sql'   => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'                  => [
            'label' => 'pid.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'              => [
            'label'   => 'sorting.label',
            'sorting' => true,
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'               => [
            'label' => 'tstamp.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'fid'                  => [
            // Keep this empty but keep it here!
            // needed for act=copy in DC_Table, as otherwise the fid value will not be copied.
            'label'       => 'fid.label',
            'description' => 'fid.description',
            'sql'         => "int(10) unsigned NOT NULL default '0'"
        ],
        'type'                 => [
            'label'       => 'type.label',
            'description' => 'type.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'doNotSaveEmpty'     => true,
                'alwaysSave'         => true,
                'submitOnChange'     => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'chosen'             => true
            ],
            'sql'         => "varchar(64) NOT NULL default ''"
        ],
        'enabled'              => [
            'label'       => 'enabled.label',
            'description' => 'enabled.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'default'     => 1,
            'eval'        => [
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12 cbx',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'comment'              => [
            'label'       => 'comment.label',
            'description' => 'comment.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'eval'        => ['tl_class' => 'clr long'],
            'sql'         => "varchar(255) NOT NULL default ''"
        ],
        'attr_id'              => [
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
                'tl_class'           => 'w50',
                'chosen'             => true
            ],
            'sql'         => "int(10) unsigned NOT NULL default '0'"
        ],
        'all_langs'            => [
            'label'       => 'all_langs.label',
            'description' => 'all_langs.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12 cbx',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'items'                => [
            'label'       => 'items.label',
            'description' => 'items.description',
            'exclude'     => true,
            'inputType'   => 'textarea',
            'eval'        => [
                'doNotSaveEmpty' => true,
                'alwaysSave'     => true,
                'mandatory'      => true,
            ],
            'sql'         => 'text NULL'
        ],
        'urlparam'             => [
            'label'       => 'urlparam.label',
            'description' => 'urlparam.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'eval'        => [
                'tl_class' => 'w50',
            ],
            'sql'         => "varchar(255) NOT NULL default ''"
        ],
        'predef_param'         => [
            'label'       => 'predef_param.label',
            'description' => 'predef_param.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12 cbx',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'fe_widget'            => [
            'label'       => 'fe_widget.label',
            'description' => 'fe_widget.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12 cbx',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'customsql'            => [
            'label'       => 'customsql.label',
            'description' => 'customsql.description',
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
                'helptext'       => true,
            ],
            'sql'         => 'text NULL'
        ],
        'allow_empty'          => [
            'label'       => 'allow_empty.label',
            'description' => 'allow_empty.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12 cbx',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'stop_after_match'     => [
            'label'       => 'stop_after_match.label',
            'description' => 'stop_after_match.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'alwaysSave' => true,
                'tl_class'   => 'w50 cbx',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'label'                => [
            'label'       => 'label.label',
            'description' => 'label.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'eval'        => [
                'tl_class' => 'clr w50',
            ],
            'sql'         => 'blob NULL'
        ],
        'template'             => [
            'label'       => 'template.label',
            'description' => 'template.description',
            'default'     => 'mm_filteritem_default',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'tl_class' => 'w50',
                'chosen'   => true
            ],
            'sql'         => "varchar(64) NOT NULL default ''"
        ],
        'blankoption'          => [
            'label'       => 'blankoption.label',
            'description' => 'blankoption.description',
            'exclude'     => true,
            'default'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'clr w50 m12 cbx',
            ],
            'sql'         => "char(1) NOT NULL default '1'"
        ],
        'onlyused'             => [
            'label'       => 'onlyused.label',
            'description' => 'onlyused.description',
            'exclude'     => true,
            'default'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class'       => 'w50 m12 cbx',
                'submitOnChange' => true,
            ],
            'sql'         => "char(1) NOT NULL default '1'"
        ],
        'onlypossible'         => [
            'label'       => 'onlypossible.label',
            'description' => 'onlypossible.description',
            'exclude'     => true,
            'default'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 m12 cbx',
            ],
            'sql'         => "char(1) NOT NULL default '1'"
        ],
        'skipfilteroptions'    => [
            'label'       => 'skipfilteroptions.label',
            'description' => 'skipfilteroptions.description',
            'exclude'     => true,
            'default'     => false,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 m12 cbx',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'defaultid'            => [
            'label'       => 'defaultid.label',
            'description' => 'defaultid.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'tl_class'           => 'clr w50',
                'includeBlankOption' => true
            ],
            'sql'         => "varchar(255) NOT NULL default ''"
        ],
        'hide_label'           => [
            'label'       => 'hide_label.label',
            'description' => 'hide_label.description',
            'exclude'     => true,
            'default'     => false,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 m12 cbx',
            ],
            'sql'         => "char(1) NOT NULL default '0'"
        ],
        'label_as_blankoption' => [
            'label'       => 'label_as_blankoption.label',
            'description' => 'label_as_blankoption.description',
            'exclude'     => true,
            'default'     => false,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 m12 cbx',
            ],
            'sql'         => "char(1) NOT NULL default '0'"
        ],
        'apply_sorting'        => [
            'label'       => 'apply_sorting.label',
            'description' => 'apply_sorting.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'options'     => ['natsort_asc', 'natsort_desc'],
            'reference'   => [
                'natsort_asc'  => 'sorting_directions.natsort_asc',
                'natsort_desc' => 'sorting_directions.natsort_desc',
            ],
            'eval'        => [
                'tl_class'           => 'w50',
                'includeBlankOption' => true
            ],
            'sql'         => ['type' => 'string', 'length' => '24', 'notnull' => false, 'default' => '']
        ],
        'cssID'                => [
            'label'       => 'cssID.label',
            'description' => 'cssID.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'eval'        => [
                'multiple' => true,
                'size'     => 2,
                'tl_class' => 'clr w50'
            ],
            'sql'         => "varchar(255) NOT NULL default ''"
        ],
        'placeholder'          => [
            'label'       => 'placeholder.label',
            'description' => 'placeholder.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'sql'         => 'varchar(255) NOT NULL default \'\'',
            'eval'        => ['tl_class' => 'w50']
        ],
        'use_only_in_env'      => [
            'label'       => 'use_only_in_env.label',
            'description' => 'use_only_in_env.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'tl_class'           => 'clr w50',
                'includeBlankOption' => true
            ],
            'sql'         => "varchar(255) NOT NULL default ''"
        ],
    ]
];
