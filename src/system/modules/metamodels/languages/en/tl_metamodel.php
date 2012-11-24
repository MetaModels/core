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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel']['name']                 = array('Name', 'MetaModel name.');
$GLOBALS['TL_LANG']['tl_metamodel']['tstamp']               = array('Revision date', 'Date and time of the latest revision');
$GLOBALS['TL_LANG']['tl_metamodel']['tableName']            = array('Table name', 'Name of database table to store items to.');

$GLOBALS['TL_LANG']['tl_metamodel']['mode']                 = array('Parent list mode', 'Mode to use for parent/child relationship.');

$GLOBALS['TL_LANG']['tl_metamodel']['translated']           = array('Translation', 'Check if this MetaModel shall support translation/multilingualism.');
$GLOBALS['TL_LANG']['tl_metamodel']['languages']            = array('Languages to provide for translation', 'Specify all languages that shall be available for translation.');
$GLOBALS['TL_LANG']['tl_metamodel']['languages_langcode']   = array('Language', 'Select the languages you want to provide.');
$GLOBALS['TL_LANG']['tl_metamodel']['languages_isfallback'] = array('Fallback language', 'Check the language that shall be used as fallback.');

$GLOBALS['TL_LANG']['tl_metamodel']['varsupport']           = array('Variant support', 'Check if this MetaModel shall support variants of items.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel']['title_legend']         = 'Name, table and redirect page';
$GLOBALS['TL_LANG']['tl_metamodel']['advanced_legend']      = 'Advanced settings';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel']['new']                  = array('New MetaModel', 'Create a new MetaModel.');

$GLOBALS['TL_LANG']['tl_metamodel']['edit']                 = array('Manage items', 'Manage items of MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['copy']                 = array('Copy MetaModel definition', 'Copy definition of MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['delete']               = array('Delete MetaModel', 'Delete MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['show']                 = array('MetaModel details', 'Show details of MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['editheader']           = array('Edit MetaModel', 'Edit the MetaModel');
$GLOBALS['TL_LANG']['tl_metamodel']['fields']               = array('Define attributes', 'Define attributes for MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['filter']               = array('Define filters', 'Define filters for MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['rendersettings']       = array('Define render settings', 'Define render settings for MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['dca']                  = array('Define palettes', 'Define palettes for MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['dca_combine']          = array('Define palette and view combinations', 'Define palette and view combinations for MetaModel ID %s');

/**
 * Misc.
 */
$GLOBALS['TL_LANG']['tl_metamodel']['itemFormat'] = '%s %s';
$GLOBALS['TL_LANG']['tl_metamodel']['itemSingle'] = 'item';
$GLOBALS['TL_LANG']['tl_metamodel']['itemPlural'] = 'items';


?>