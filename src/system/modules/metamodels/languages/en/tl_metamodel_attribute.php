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
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['type']                 = array('Attribute type', 'Select the type of this attribute. WARNING! if you change this, all existing data within this attribute will be deleted.');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name']                 = array('Name', 'Human readable name');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['description']          = array('Description', 'Human readable description');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['colname']              = array('Column Name', 'Internal reference name for this attribute');

$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isvariant']            = array('Enable variant override', 'Check this, if you want variants within the MetaModel to override the parent\'s value');

$GLOBALS['TL_LANG']['tl_metamodel_attribute']['unique']            = array('Unique values', 'Check this, if you want to ensure that each value only occurs once');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['title_legend']         = 'Type, naming and base attribute configuration';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['advanced_legend']      = 'Advanced settings';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['new']                  = array('New attribute', 'Create new Attribute.');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['edit']                 = array('Edit attribute', 'Edit attribute ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['cut']                  = array('Cut attribute definiton', 'Cut definition of attribute ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['copy']                 = array('Copy attribute definiton', 'Copy definition of attribute ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['delete']               = array('Delete attribute', 'Delete attribute ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['show']                 = array('Attribute details', 'Show details of attribute ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['editheader']           = array('Edit attribute', 'Edit the attribute');

?>