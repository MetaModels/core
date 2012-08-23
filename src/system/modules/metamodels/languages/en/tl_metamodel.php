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
$GLOBALS['TL_LANG']['tl_metamodel']['name']                 = array('Name', 'MetaModel name.');
$GLOBALS['TL_LANG']['tl_metamodel']['tstamp']               = array('Revision date', 'Date and time of the latest revision');
$GLOBALS['TL_LANG']['tl_metamodel']['tableName']            = array('Table name', 'Name of database table to store items to.');

$GLOBALS['TL_LANG']['tl_metamodel']['ptable']               = array('Parent table name (if any)', 'Name of the database table that shall be referred to as parent table.');
$GLOBALS['TL_LANG']['tl_metamodel']['mode']                 = array('Parent list mode', 'Mode to use for parent/child relationship.');

$GLOBALS['TL_LANG']['tl_metamodel']['translated']           = array('Translation', 'Check if this MetaModel shall support translation/multilanguage.');
$GLOBALS['TL_LANG']['tl_metamodel']['languages']            = array('Languages to provide for translation', 'Specify all languages that shall be available for translation.');
$GLOBALS['TL_LANG']['tl_metamodel']['languages_langcode']   = array('Language', '');
$GLOBALS['TL_LANG']['tl_metamodel']['languages_isfallback'] = array('Fallback language', '');

$GLOBALS['TL_LANG']['tl_metamodel']['varsupport']           = array('Variant support', 'Check if this MetaModel shall support variants of items.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel']['title_legend']         = 'Name, table and redirect page';
$GLOBALS['TL_LANG']['tl_metamodel']['advanced_legend']      = 'Advanced settings';
$GLOBALS['TL_LANG']['tl_metamodel']['display_legend']       = 'Display format';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel']['new']                  = array('New MetaModel', 'Create new MetaModel.');

$GLOBALS['TL_LANG']['tl_metamodel']['edit']                 = array('Manage items', 'Manage items of MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['copy']                 = array('Copy MetaModel definiton', 'Copy definition of MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['delete']               = array('Delete MetaModel', 'Delete MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['show']                 = array('MetaModel details', 'Show details of MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['editheader']           = array('Edit MetaModel', 'Edit the MetaModel');
$GLOBALS['TL_LANG']['tl_metamodel']['fields']               = array('Define attributes', 'Define attributes for MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['filter']               = array('Define filters', 'Define filters for MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['rendersettings']       = array('Define render settings', 'Define render settings for MetaModel ID %s');

/**
 * Misc.
 */
$GLOBALS['TL_LANG']['tl_metamodel']['itemFormat'] = ' <span style="color:#b3b3b3;"><em>(%s %s)</em></span>';
$GLOBALS['TL_LANG']['tl_metamodel']['itemSingle'] = 'item';
$GLOBALS['TL_LANG']['tl_metamodel']['itemPlural'] = 'items';


?>