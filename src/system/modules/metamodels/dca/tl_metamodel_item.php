<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/*
    This file defines the basic structure of ALL MetaModels.
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
        'controller'                  => 'MetaModels\DcGeneral\Controller',
    ),

    'list' => array
    (
        'sorting' => array
        (
            // This means: 1 default sorting value, 2 switchable sorting value.
            'mode'                    => 1,
            'headerFields'            => array('tstamp'),
        ),

        'label' => array
        (
            'fields'                  => array(),
            'format'                  => '%s',
        ),
    ),

    'fields' => array
    (
        'id' => array(),
        'pid' => array(),
        'sorting' => array
        (
            'sorting'                 => true,
            'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_item']['sorting'],
        ),
        'tstamp' => array()
    )
);

