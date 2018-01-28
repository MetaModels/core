<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['basic_legend']                                    =
    'Basic configuration';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['config_legend']                                   =
    'Condition configuration';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['type'][0]                                         = 'Type';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['type'][1]                                         =
    'Select the condition type.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['enabled'][0]                                      = 'Enabled';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['enabled'][1]                                      =
    'Check to enable this condition.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['comment'][0]                                      = 'Comment';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['comment'][1]                                      =
    'Enter a comment to describe the purpose of this condition.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['attr_id'][0]                                      =
    'Attribute';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['attr_id'][1]                                      =
    'Select the attribute to use for this condition.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['value'][0]                                        = 'Value';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['value'][1]                                        =
    'Please select the desired value.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['new'][0]                                          = 'New';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['new'][1]                                          =
    'Create new setting.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['edit'][0]                                         =
    'Edit setting';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['edit'][1]                                         =
    'Edit setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['cut'][0]                                          =
    'Cut setting definition';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['cut'][1]                                          =
    'Cut setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['copy'][0]                                         =
    'Copy setting definition';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['copy'][1]                                         =
    'Copy setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['delete'][0]                                       =
    'Delete setting';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['delete'][1]                                       =
    'Delete setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditions'][0]                                   =
    'Manage conditions';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditions'][1]                                   =
    'Manage the conditions of property ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['show'][0]                                         =
    'Setting details';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['show'][1]                                         =
    'Show details of setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['toggle'][0]                                       = 'Toggle';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['toggle'][1]                                       =
    'Toggle the state of setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['addall'][0]                                       = 'Add all';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['addall'][1]                                       =
    'Add all attributes to input screen';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['pastenew'][0]                                     =
    'Add new at the top';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['pastenew'][1]                                     =
    'Add new after setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['pasteafter'][1]                                   =
    'Create new after setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['pasteinto'][0]                                    =
    'Create new setting at the top';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['pasteinto'][1]                                    =
    'Create new at the top of setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionpropertyvalueis']      =
    'Attribute value is...';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionpropertycontainanyof'] =
    'Attribute values contain any of...';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionpropertyvisible']      =
    'Is attribute visible...';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionor']                   = 'OR';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionand']                  = 'AND';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionnot']                  = 'NOT';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['typedesc']['_default_']                           =
    '%s <strong>%s</strong><br>for attribute <em>%s</em> (Parameter: %s)';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['typedesc']['conditionor']                         =
    '%s <strong>%s</strong><br>any sub conditions must be fulfilled';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['typedesc']['conditionand']                        =
    '%s <strong>%s</strong><br>all sub conditions must be fulfilled';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['typedesc']['conditionnot']                        =
    '%s <strong>%s</strong><br>invert the result of the contained condition';
