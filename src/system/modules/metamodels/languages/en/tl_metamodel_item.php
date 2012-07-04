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
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_item']['new']           = array('New item', 'Create new item.');
$GLOBALS['TL_LANG']['tl_metamodel_item']['edit']          = array('Edit item', 'Edit item ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_item']['copy']          = array('Copy item', 'Copy item ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_item']['createvariant'] = array('New variant', 'Create a new variant of item ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_item']['cut']           = array('Move item', 'Move item ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_item']['delete']        = array('Delete item', 'Delete item ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_item']['show']          = array('Item details', 'Show details of item ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_item']['editheader']    = array('Edit item type', 'Edit the item type');
$GLOBALS['TL_LANG']['tl_metamodel_item']['fields']        = array('Manage attributes', 'Manage attributes of this MetaModel');


$GLOBALS['TL_LANG']['tl_metamodel_item']['varbase']       = array('Is variant base', 'Check this if you want to make this the base for the current variant group');

?>