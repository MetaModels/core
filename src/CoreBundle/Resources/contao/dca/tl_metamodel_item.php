<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Oliver Lohoff <oliverlohoff@gmail.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

/*
    This file defines the basic structure of ALL MetaModel items.
    Note however, that various MetaModel extensions might remove or add stuff here.
*/

$GLOBALS['TL_DCA']['tl_metamodel_item'] = array
(
    'config' => array
    (
        'dataContainer'               => 'General',
        'switchToEdit'                => false,
        'enableVersioning'            => false,
    ),
    'dca_config' => array
    (
        'data_provider'               => array
        (
            'default'                 => array
            (
                'class'               => 'MetaModels\DcGeneral\Data\Driver',
            )
        ),
    ),

    'list' => array
    (
        'sorting' => array
        (
            // This means: 1 default sorting value, 2 switchable sorting value.
            'mode'                    => 1,
            'headerFields'            => array
            (
                'tstamp'
            ),
        ),

        'label' => array
        (
            'fields'                  => array
            (
            ),
            'format'                  => '%s',
        ),
    ),

    'fields' => array
    (
        'id' => array
        (
        ),
        'pid' => array
        (
        ),
        'sorting' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_item']['sorting'],
        ),
        'tstamp' => array
        (
        )
    )
);
