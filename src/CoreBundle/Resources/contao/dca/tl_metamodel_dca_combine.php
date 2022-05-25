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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
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
        'data_provider' => [
            'default' => [
                'class'        => 'ContaoCommunityAlliance\DcGeneral\Data\TableRowsAsRecordsDataProvider',
                'source'       => 'tl_metamodel_dca_combine',
                'group_column' => 'pid',
                'sort_column'  => 'sorting'
            ]
        ],
        'childCondition' => [
            [
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_dca_combine',
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
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'      => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'  => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'   => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'rows'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_combiner'],
            'exclude'   => true,
            'inputType' => 'multiColumnWizard',
            'eval'      => [
                'tl_class'     => 'dca_combine',
                'columnFields' => [
                    'id'       => [
                        'label'     => null,
                        'exclude'   => true,
                        'inputType' => 'justtext',
                        'eval'      => [
                            'hideHead' => true,
                            'hideBody' => true,
                        ]
                    ],
                    'fe_group' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['fe_group'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'eval'      => [
                            'includeBlankOption' => true,
                            'blankOptionLabel'   => '*',
                            'style'              => 'width:100%',
                            'chosen'             => 'true'
                        ],
                    ],
                    'be_group' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['be_group'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'eval'      => [
                            'includeBlankOption' => true,
                            'blankOptionLabel'   => '*',
                            'style'              => 'width:100%',
                            'chosen'             => 'true'
                        ],
                    ],
                    'view_id'  => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['view_id'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'eval'      =>
                            [
                            'includeBlankOption' => true,
                            'style'              => 'width:100%',
                            'chosen'             => 'true'
                            ],
                    ],
                    'dca_id'   => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_id'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'eval'      =>
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
            // keep signed as anonymous are -1
            'sql' => "int(10) NOT NULL default '0'"
        ],
        'be_group' => [
            // keep signed as admins are -1
            'sql' => "int(10) NOT NULL default '0'"
        ],
        'view_id'  => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'dca_id'   => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ]
    ]
];
