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
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ]
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg'
            ],
            'cut'    => [
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['cut'],
                'href'       => 'act=paste&amp;mode=cut',
                'icon'       => 'cut.svg',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['tl_metamodel_attribute']['deleteConfirm'] ?? ''
                )
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
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
            'metamodeloverview' => [],
            'backenddisplay'    => [],
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
            'label' => 'id.0',
            'sql'   => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'         => [
            'label' => 'pid.0',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'     => [
            'label'   => 'sorting.0',
            'sorting' => true,
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'      => [
            'label' => 'tstamp.0',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'type'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['type'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'includeBlankOption' => true,
                'doNotSaveEmpty'     => true,
                'alwaysSave'         => true,
                'submitOnChange'     => true,
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'chosen'             => true
            ],
            'filter'    => true,
            'search'    => true,
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'name'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'tl_class' => 'clr'
            ],
            'search'    => true,
            'sql'       => 'text NULL'
        ],
        'description' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['description'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'tl_class' => 'clr'
            ],
            'sql'       => 'text NULL'
        ],
        // AVOID: doNotCopy => true, as child records won't be copied when copy metamodel.
        'colname'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['colname'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'mandatory'             => true,
                'maxlength'             => 64,
                'tl_class'              => 'w50',
                // Hide at overrideAll.
                'doNotOverrideMultiple' => true
            ],
            'search'    => true,
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'isvariant'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isvariant'],
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50 cbx m12'
            ],
            'filter'    => true,
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'isunique'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isunique'],
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'w50 cbx m12'
            ],
            'sql'       => "char(1) NOT NULL default ''"
        ],
    ]
];
