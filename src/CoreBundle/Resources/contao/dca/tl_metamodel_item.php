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
 * @author     Oliver Lohoff <oliverlohoff@gmail.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/*
    This file defines the basic structure of ALL MetaModel items.
    Note however, that various MetaModel extensions might remove or add stuff here.
*/
$GLOBALS['TL_DCA']['tl_metamodel_item'] = [
    'config'     => [
        'dataContainer'    => 'General',
        'switchToEdit'     => false,
        'enableVersioning' => false,
    ],
    'dca_config' => [
        'data_provider' => [
            'default' => [
                'class' => 'MetaModels\DcGeneral\Data\Driver',
            ]
        ],
    ],
    'list'       => [
        'sorting' => [
            // This means: 1 default sorting value, 2 switchable sorting value.
            'mode'         => 1,
            'headerFields' => [
                'tstamp'
            ],
        ],

        'label' => [
            'fields' => [
            ],
            'format' => '%s',
        ],
    ],
    'fields'     => [
        'id'      => [
            'label' => 'id.0',
        ],
        'pid'     => [
            'label' => 'pid.0',
        ],
        'sorting' => [
            'label' => 'sorting.0',
        ],
        'tstamp'  => [
            'label' => 'tstamp.0',
        ]
    ]
];
