<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['basic_legend']  = 'Basic configuration';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['config_legend'] = 'Condition configuration';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['type']    = array('Type', 'Select the condition type.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['enabled'] = array('Enabled', 'Check to enable this condition.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['comment'] = array('Comment', 'Enter a comment to describe the purpose of this condition.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['attr_id'] = array('Attribute', 'Select the attribute to use for this condition.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['value']   = array('Value', 'Please select the desired value.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['new']        = array('New', 'Create new setting.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['edit']       = array('Edit setting', 'Edit setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['copy']       = array('Copy setting definition', 'Copy setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['delete']     = array('Delete setting', 'Delete setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditions'] = array('Manage conditions', 'Manage the conditions of property ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['show']       = array('Setting details', 'Show details of setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['addall']     = array('Add all', 'Add all attributes to input screen');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['pastenew'][0]   = 'Add new at the top';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['pastenew'][1]   = 'Add new after setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['pasteafter'][1] = 'Create new after setting ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['pasteinto'][0] = 'Create new setting at the top';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['pasteinto'][1] = 'Create new at the top of setting ID %s';

// filter condition names.
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionpropertyvalueis']      = 'Attribute value is...';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionpropertycontainanyof'] = 'Attribute values contain any of...';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionpropertyvisible']      = 'Is attribute visible...';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionor']                   = 'OR';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionand']                  = 'AND';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionnot']                  = 'NOT';

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['typedesc']['_default_']    = '%s <strong>%s</strong><br>for attribute <em>%s</em> (Parameter: %s)';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['typedesc']['conditionor']  = '%s <strong>%s</strong><br>any sub conditions must be fulfilled';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['typedesc']['conditionand'] = '%s <strong>%s</strong><br>all sub conditions must be fulfilled';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['typedesc']['conditionnot'] = '%s <strong>%s</strong><br>invert the result of the contained condition';
