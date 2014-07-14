<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['title_legend']  = 'Title';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['config_legend'] = 'Config';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['type']    = array('Type', 'Select the attribute type.');
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

// filter condition names.
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionor']              = 'OR';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionand']             = 'AND';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionpropertyvalueis'] = 'Property value is';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionpropertyvisible'] = 'Property is visible';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['conditionnames']['conditionnot']             = 'Not';

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['typedesc']['_default_']    = '%s <strong>%s</strong><br /> for attribute <em>%s</em> (Parameter: %s)';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting_condition']['typedesc']['conditionnot'] = '%s <strong>%s</strong><br /> invert the result of the contained conditions.';
