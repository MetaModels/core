<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2016 The MetaModels team.
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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_dca_combine'] = array
(
    'config'     => array
    (
        'dataContainer'    => 'General',
        'ptable'           => 'tl_metamodel',
        'switchToEdit'     => false,
        'enableVersioning' => false,
        'closed'           => false,
    ),
    'dca_config' => array
    (
        'data_provider' => array
        (
            'default' => array
            (
                'class'        => 'ContaoCommunityAlliance\DcGeneral\Data\TableRowsAsRecordsDataProvider',
                'source'       => 'tl_metamodel_dca_combine',
                'group_column' => 'pid',
                'sort_column'  => 'sorting'
            )
        ),
    ),
    'palettes'   => array
    (
        'default' => 'rows'
    ),
    'fields'     => array
    (
        'rows' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_combiner'],
            'exclude'   => true,
            'inputType' => 'multiColumnWizard',
            'eval'      => array
            (
                'tl_class'     => 'dca_combine',
                'columnFields' => array
                (
                    'id'       => array
                    (
                        'label'     => null,
                        'exclude'   => true,
                        'inputType' => 'justtext',
                        'eval'      => array
                        (
                            'hideHead' => true,
                            'hideBody' => true,
                        )
                    ),
                    'fe_group' => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['fe_group'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'eval'      => array
                        (
                            'includeBlankOption' => true,
                            'style'              => 'width:115px',
                            'chosen'             => 'true'
                        )
                    ),
                    'be_group' => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['be_group'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'eval'      => array
                        (
                            'includeBlankOption' => true,
                            'style'              => 'width:115px',
                            'chosen'             => 'true'
                        )
                    ),
                    'dca_id'   => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_id'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'eval'      => array
                        (
                            'includeBlankOption' => true,
                            'style'  => 'width:180px',
                            'chosen' => 'true'
                        )
                    ),
                    'view_id'  => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['view_id'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'eval'      => array
                        (
                            'includeBlankOption' => true,
                            'style'  => 'width:180px',
                            'chosen' => 'true'
                        )
                    ),
                ),
            ),
        )
    )
);
