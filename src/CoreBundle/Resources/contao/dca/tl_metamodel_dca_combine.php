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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_dca_combine'] = [
    'config'     => [
        'dataContainer'    => 'General',
        'ptable'           => 'tl_metamodel',
        'switchToEdit'     => false,
        'enableVersioning' => false,
        'closed'           => false,
        'sql'              => [
            'keys' => [
                'id'       => 'primary',
                'pid'      => 'index',
                'fe_group' => 'index',
                'be_group' => 'index'
            ],
        ]
    ],
    'dca_config' => [
        'data_provider'  => [
            'default' => [
                'class'        => 'ContaoCommunityAlliance\DcGeneral\Data\TableRowsAsRecordsDataProvider',
                'source'       => 'tl_metamodel_dca_combine',
                'group_column' => 'pid',
                'sort_column'  => 'sorting'
            ]
        ],
        'childCondition' => [
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
                // Not set a inverse filter for this configuration.
                // The used data provider not supported a parent data provider configuration.
                // 'inverse' => []
            ],
        ],
    ],
    'palettes'   => [
        'default' => '{dca_combiner_legend},rows'
    ],
    'fields'     => [
        'id'       => [
            'label' => 'id.label',
            'sql'   => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'      => [
            'label' => 'pid.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'  => [
            'label' => 'sorting.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'   => [
            'label' => 'tstamp.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'rows'     => [
            'label'       => 'dca_combiner.label',
            'description' => 'dca_combiner.description',
            'exclude'     => true,
            'inputType'   => 'multiColumnWizard',
            'eval'        => [
                'useTranslator' => true,
                'tl_class'      => 'dca_combine',
                'columnFields'  => [
                    'id'       => [
                        'label'     => 'id.label',
                        'exclude'   => true,
                        'inputType' => 'justtext',
                        'eval'      => [
                            'hideHead' => true,
                            'hideBody' => true,
                        ]
                    ],
                    'fe_group' => [
                        'label'       => 'fe_group.label',
                        'description' => 'fe_group.description',
                        'exclude'     => true,
                        'inputType'   => 'select',
                        'eval'        => [
                            'includeBlankOption' => true,
                            'blankOptionLabel'   => '*',
                            'style'              => 'width:100%',
                            'chosen'             => 'true'
                        ],
                    ],
                    'be_group' => [
                        'label'       => 'be_group.label',
                        'description' => 'be_group.description',
                        'exclude'     => true,
                        'inputType'   => 'select',
                        'eval'        => [
                            'includeBlankOption' => true,
                            'blankOptionLabel'   => '*',
                            'style'              => 'width:100%',
                            'chosen'             => 'true'
                        ],
                    ],
                    'view_id'  => [
                        'label'       => 'view_id.label',
                        'description' => 'view_id.description',
                        'exclude'     => true,
                        'inputType'   => 'select',
                        'eval'        =>
                            [
                                'includeBlankOption' => true,
                                'style'              => 'width:100%',
                                'chosen'             => 'true'
                            ],
                    ],
                    'dca_id'   => [
                        'label'       => 'dca_id.label',
                        'description' => 'dca_id.description',
                        'exclude'     => true,
                        'inputType'   => 'select',
                        'eval'        =>
                            [
                                'includeBlankOption' => true,
                                'style'              => 'width:100%',
                                'chosen'             => 'true'
                            ],
                    ],
                ],
            ],
        ],
        'fe_group' => [
            'label' => 'fe_group.label',
            // keep signed as anonymous are -1
            'sql'   => "int(10) NOT NULL default '0'"
        ],
        'be_group' => [
            'label' => 'be_group.label',
            // keep signed as admins are -1
            'sql'   => "int(10) NOT NULL default '0'"
        ],
        'view_id'  => [
            'label' => 'view_id.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'dca_id'   => [
            'label' => 'dca_id.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ]
    ]
];
