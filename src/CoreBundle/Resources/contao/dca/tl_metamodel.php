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
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tim Becker <please.tim@metamodel.me>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel'] = [
    'config'          => [
        'dataContainer'    => 'General',
        'switchToEdit'     => true,
        'enableVersioning' => false,
        'sql'              => [
            'keys' => [
                'id'        => 'primary',
                'tableName' => 'unique',
            ],
        ],
    ],
    'dca_config'      => [
        'data_provider'  => [
            'default' => [
                'source' => 'tl_metamodel'
            ],

            'tl_metamodel_attribute' => [
                'source' => 'tl_metamodel_attribute'
            ],

            'tl_metamodel_rendersettings' => [
                'source' => 'tl_metamodel_rendersettings'
            ],
            'tl_metamodel_rendersetting'  => [
                'source' => 'tl_metamodel_rendersetting'
            ],

            'tl_metamodel_dca'                  => [
                'source' => 'tl_metamodel_dca'
            ],
            'tl_metamodel_dca_sortgroup'        => [
                'source' => 'tl_metamodel_dca_sortgroup'
            ],
            'tl_metamodel_dcasetting'           => [
                'source' => 'tl_metamodel_dcasetting'
            ],
            'tl_metamodel_dcasetting_condition' => [
                'source' => 'tl_metamodel_dcasetting_condition'
            ],

            'tl_metamodel_searchable_pages' => [
                'source' => 'tl_metamodel_searchable_pages'
            ],

            'tl_metamodel_filter'        => [
                'source' => 'tl_metamodel_filter'
            ],
            'tl_metamodel_filtersetting' => [
                'source' => 'tl_metamodel_filtersetting'
            ],

            'tl_metamodel_dca_combine' => [
                'source' => 'tl_metamodel_dca_combine'
            ],
        ],
        'childCondition' => [
            [
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_attribute',
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
                'filter' => [
                    [
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ]
            ],
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
                ]
            ],
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
            ],
            [
                'from'   => 'tl_metamodel',
                'to'     => 'tl_metamodel_filter',
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
                'from'   => 'tl_metamodel',
                'to'     => 'tl_metamodel_dca_combine',
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
        ],
    ],
    'list'            => [
        'sorting'           => [
            'mode'        => 2,
            'fields'      => [],
            'flag'        => 1,
            'panelLayout' => 'sort,search;limit'
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
            'edit'             => [
                'label'       => 'editheader.label',
                'description' => 'editheader.description',
                'href'        => 'act=edit',
                'icon'        => 'edit.svg',
            ],
            'cut'              => [
                'label'       => 'cut.label',
                'description' => 'cut.description',
                'href'        => 'act=cut',
                'icon'        => 'cut.svg'
            ],
            'delete'           => [
                'label'       => 'delete.label',
                'description' => 'delete.description',
                'href'        => 'act=delete',
                'icon'        => 'delete.svg',
                'attributes'  => 'onclick="if (!confirm(this.dataset.msgConfirm)) return false; Backend.getScrollOffset();"',
            ],
            'show'             => [
                'label'       => 'show.label',
                'description' => 'show.description',
                'href'        => 'act=show',
                'icon'        => 'show.svg'
            ],
            'fields'           => [
                'label'       => 'fields.label',
                'description' => 'fields.description',
                'href'        => 'table=tl_metamodel_attribute',
                'icon'        => '/bundles/metamodelscore/images/icons/fields.png',
                'idparam'     => 'pid'
            ],
            'rendersettings'   => [
                'label'       => 'rendersettings.label',
                'description' => 'rendersettings.description',
                'href'        => 'table=tl_metamodel_rendersettings',
                'icon'        => '/bundles/metamodelscore/images/icons/rendersettings.png',
                'idparam'     => 'pid'
            ],
            'dca'              => [
                'label'       => 'dca.label',
                'description' => 'dca.description',
                'href'        => 'table=tl_metamodel_dca',
                'icon'        => '/bundles/metamodelscore/images/icons/dca.png',
                'idparam'     => 'pid'
            ],
            'searchable_pages' => [
                'label'       => 'searchable_pages.label',
                'description' => 'searchable_pages.description',
                'href'        => 'table=tl_metamodel_searchable_pages',
                'icon'        => '/bundles/metamodelscore/images/icons/searchable_pages.png',
                'idparam'     => 'pid'
            ],
            'filter'           => [
                'label'       => 'filter.label',
                'description' => 'filter.description',
                'href'        => 'table=tl_metamodel_filter',
                'icon'        => '/bundles/metamodelscore/images/icons/filter.png',
                'idparam'     => 'pid'
            ],
            'dca_combine'      => [
                'label'       => 'dca_combine.label',
                'description' => 'dca_combine.description',
                'href'        => 'table=tl_metamodel_dca_combine&act=edit',
                'icon'        => '/bundles/metamodelscore/images/icons/dca_combine.png',
                'idparam'     => 'pid'
            ],
        ]
    ],
    'metapalettes'    => [
        'default' => [
            'title'      => [
                'name',
                'tableName'
            ],
            'translated' => [
                ':hide',
                'translated'
            ],
            'advanced'   => [
                ':hide',
                'varsupport'
            ],
        ]
    ],
    'metasubpalettes' => [
        'translated' => [
            'localeterritorysupport',
            'languages'
        ],
    ],
    'fields'          => [
        'id'                     => [
            'label'       => 'id.label',
            'description' => 'id.description',
            'sql'         => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'tstamp'                 => [
            'label'       => 'tstamp.label',
            'description' => 'tstamp.description',
            'sql'         => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'                => [
            'label'       => 'sorting.label',
            'description' => 'sorting.description',
            'sorting'     => true,
            'flag'        => 11,
            'sql'         => "int(10) unsigned NOT NULL default '0'"
        ],
        'name'                   => [
            'label'       => 'name.label',
            'description' => 'name.description',
            'sorting'     => true,
            'flag'        => 3,
            'length'      => 1,
            'exclude'     => true,
            'inputType'   => 'text',
            'search'      => true,
            'eval'        => [
                'mandatory'         => true,
                'useRawRequestData' => true,
                'maxlength'         => 255,
                'tl_class'          => 'w50'
            ],
            'sql'         => "varchar(255) NOT NULL default ''"
        ],
        'tableName'              => [
            'label'       => 'tableName.label',
            'description' => 'tableName.description',
            'sorting'     => true,
            'exclude'     => true,
            'inputType'   => 'text',
            'search'      => true,
            'eval'        => [
                'mandatory'             => true,
                'maxlength'             => 64,
                'doNotCopy'             => true,
                'tl_class'              => 'w50',
                // Hide at overrideAll.
                'doNotOverrideMultiple' => true
            ],
            'sql'         => "varchar(64) NOT NULL default ''"
        ],
        'translated'             => [
            'label'       => 'translated.label',
            'description' => 'translated.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class'       => 'clr w50 cbx m12',
                'submitOnChange' => true
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'languages'              => [
            'label'       => 'languages.label',
            'description' => 'languages.description',
            'exclude'     => true,
            'inputType'   => 'multiColumnWizard',
            'eval'        =>
                [
                    'useTranslator' => true,
                    'tl_class'      => 'clr w50',
                    'columnFields'  => [
                        'langcode'   => [
                            'label'       => 'languages_langcode.label',
                            'description' => 'languages_langcode.description',
                            'exclude'     => true,
                            'inputType'   => 'select',
                            'eval'        => [
                                'style'  => 'width:100%;',
                                'chosen' => 'true'
                            ],
                        ],
                        'isfallback' => [
                            'label'       => 'languages_isfallback.label',
                            'description' => 'languages_isfallback.description',
                            'exclude'     => true,
                            'inputType'   => 'checkbox',
                            'eval'        => [
                                'style' => 'width:100%;',
                            ],
                        ],
                    ],
                ],
            'sql'         => 'text NULL'
        ],
        'varsupport'             => [
            'label'       => 'varsupport.label',
            'description' => 'varsupport.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'clr w50'
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'localeterritorysupport' => [
            'label'       => 'localeterritorysupport.label',
            'description' => 'localeterritorysupport.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class'       => 'w50 cbx m12',
                'submitOnChange' => true
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
    ],
];
