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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute'] = [
    'config'       => [
        'dataContainer'    => 'General',
        'ptable'           => 'tl_metamodel',
        'switchToEdit'     => false,
        'enableVersioning' => false,
        'sql'              => [
            'keys' => [
                'id'          => 'primary',
                'pid'         => 'index',
                'colname'     => 'index',
                'pid,colname' => 'unique',
            ],
        ],
    ],
    'dca_config'   => [
        'data_provider'  => [
            'default'                           => [
                'source' => 'tl_metamodel_attribute'
            ],
            'parent'                            => [
                'source' => 'tl_metamodel'
            ],
            'tl_metamodel_rendersetting'        => [
                'source' => 'tl_metamodel_rendersetting'
            ],
            'tl_metamodel_dcasetting'           => [
                'source' => 'tl_metamodel_dcasetting'
            ],
            'tl_metamodel_dcasetting_condition' => [
                'source' => 'tl_metamodel_dcasetting_condition'
            ],
            'tl_metamodel_dca_sortgroup'        => [
                'source' => 'tl_metamodel_dca_sortgroup'
            ]
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
                'from'   => 'tl_metamodel_attribute',
                'to'     => 'tl_metamodel_rendersetting',
                'setOn'  => [
                    [
                        'to_field'   => 'attr_id',
                        'from_field' => 'id',
                    ],
                ],
                'filter' => [
                    [
                        'local'     => 'attr_id',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ]
            ],
            [
                'from'   => 'tl_metamodel_attribute',
                'to'     => 'tl_metamodel_dcasetting',
                'setOn'  => [
                    [
                        'to_field'   => 'attr_id',
                        'from_field' => 'id',
                    ],
                ],
                'filter' => [
                    [
                        'local'     => 'attr_id',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ]
            ],
            [
                'from'   => 'tl_metamodel_attribute',
                'to'     => 'tl_metamodel_dcasetting_condition',
                'setOn'  => [
                    [
                        'to_field'   => 'attr_id',
                        'from_field' => 'id',
                    ],
                ],
                'filter' => [
                    [
                        'local'     => 'attr_id',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ]
            ],
            [
                'from'   => 'tl_metamodel_attribute',
                'to'     => 'tl_metamodel_dca_sortgroup',
                'setOn'  => [
                    [
                        'to_field'   => 'rendersortattr',
                        'from_field' => 'id',
                    ],
                ],
                'filter' => [
                    [
                        'local'     => 'rendersortattr',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ]
            ],
        ],
    ],
    'list'         => [
        'sorting'           => [
            'disableGrouping' => true,
            'mode'            => 4,
            'fields'          => ['sorting'],
            'panelLayout'     => 'filter;search;limit',
            'headerFields'    => [
                'name',
                'tableName',
                'tstamp',
                'translated',
                'varsupport'
            ],
            'flag'            => 1,
        ],
        'label'             => [
            'fields' => ['name'],
            'format' => '%s'
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
            'edit'   => [
                'label'       => 'edit.label',
                'description' => 'edit.description',
                'href'        => 'act=edit',
                'icon'        => 'edit.svg'
            ],
            'cut'    => [
                'label'       => 'cut.label',
                'description' => 'cut.description',
                'href'        => 'act=paste&amp;mode=cut',
                'icon'        => 'cut.svg',
                'attributes'  => 'onclick="Backend.getScrollOffset();"'
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
        ]
    ],
    'metapalettes' => [
        // Initial palette with only the type to be selected.
        'default'                           => [
            'title' => [
                'type'
            ]
        ],
        // Base palette for MetaModelAttribute derived types.
        '_base_ extends default'            => [
            '+title'            => [
                'colname',
                'name',
                'description'
            ],
            'advanced'          => [
                ':hide',
                'isvariant',
                'isunique'
            ],
        ],
        // Default palette for MetaModelAttributeSimple derived types.
        // WARNING: even though it is empty, we have to keep it as otherwise
        // metapalettes will have no way for deriving the palettes. - They need the index.
        '_simpleattribute_ extends _base_'  => [],
        // Default palette for MetaModelAttributeComplex derived types.
        // WARNING: even though it is empty, we have to keep it as otherwise
        // metapalettes will have no way for deriving the palettes. - They need the index.
        '_complexattribute_ extends _base_' => [],
    ],
    // Palettes.
    'palettes'     => [
        '__selector__' => [
            'type'
        ]
    ],
    // Fields.
    'fields'       => [
        'id'          => [
            'label' => 'id.label',
            'sql'   => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'         => [
            'label' => 'pid.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'     => [
            'label'   => 'sorting.label',
            'sorting' => true,
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'      => [
            'label' => 'tstamp.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'type'        => [
            'label'       => 'type.label',
            'description' => 'type.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'includeBlankOption' => true,
                'doNotSaveEmpty'     => true,
                'alwaysSave'         => true,
                'submitOnChange'     => true,
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'chosen'             => true
            ],
            'filter'      => true,
            'search'      => true,
            'sql'         => "varchar(64) NOT NULL default ''"
        ],
        'name'        => [
            'label'       => 'name.label',
            'description' => 'name.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'eval'        => [
                'tl_class' => 'clr'
            ],
            'search'      => true,
            'sql'         => 'text NULL'
        ],
        'description' => [
            'label'       => 'description.label',
            'description' => 'description.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'eval'        => [
                'tl_class' => 'clr'
            ],
            'sql'         => 'text NULL'
        ],
        // AVOID: doNotCopy => true, as child records won't be copied when copy metamodel.
        'colname'     => [
            'label'       => 'colname.label',
            'description' => 'colname.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'eval'        => [
                'mandatory'             => true,
                'maxlength'             => 64,
                'tl_class'              => 'w50',
                // Hide at overrideAll.
                'doNotOverrideMultiple' => true
            ],
            'search'      => true,
            'sql'         => "varchar(64) NOT NULL default ''"
        ],
        'isvariant'   => [
            'label'       => 'isvariant.label',
            'description' => 'isvariant.description',
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 cbx m12'
            ],
            'filter'      => true,
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'isunique'    => [
            'label'       => 'isunique.label',
            'description' => 'isunique.description',
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 cbx m12'
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
    ]
];
