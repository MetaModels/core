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
$GLOBALS['TL_LANG']['tl_metamodel']['languages_langcode']   = array('Language', 'Select the languages you want to provide');
$GLOBALS['TL_LANG']['tl_metamodel']['languages_isfallback'] = array('Fallback language', 'Check the language that shall be used as fallback');

$GLOBALS['TL_LANG']['tl_metamodel']['varsupport']           = array('Variant support', 'Check if this MetaModel shall support variants of items.');

$GLOBALS['TL_LANG']['tl_metamodel']['rendertype']           = array('Integration', 'Select the desired type of integration.');

$GLOBALS['TL_LANG']['tl_metamodel']['backendsection']       = array('Backend section', 'Select the desired backend section where you want the MetaModel appear. For models that shall be edited by end users, the "content" section most likely will be appropriate.');

$GLOBALS['TL_LANG']['tl_metamodel']['backendcaption']       = array('Backend caption', 'The text you specify in here, will get used as the label and description text in the backend menu.');

$GLOBALS['TL_LANG']['tl_metamodel']['becap_langcode']       = array('Language', 'Select the languages you want to provide');
$GLOBALS['TL_LANG']['tl_metamodel']['becap_label']          = array('Label text', 'The text you specify in here, will get used as the menu label in the backend menu.');
$GLOBALS['TL_LANG']['tl_metamodel']['becap_description']    = array('Description text', 'The text you specify in here, will get used as the description (hover title) in the backend menu.');

$GLOBALS['TL_LANG']['tl_metamodel']['backendicon']          = array('Backend icon', 'Select the desired backend icon. This icon will get used to draw an image in the left menu and on the top of the edit view in tree displays.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel']['title_legend']         = 'Name, table and redirect page';
$GLOBALS['TL_LANG']['tl_metamodel']['advanced_legend']      = 'Advanced settings';
$GLOBALS['TL_LANG']['tl_metamodel']['backend_legend']       = 'Backend integration';

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
$GLOBALS['TL_LANG']['tl_metamodel']['dca']                  = array('Define palettes', 'Define palettes for MetaModel ID %s');
$GLOBALS['TL_LANG']['tl_metamodel']['dca_combine']          = array('Define palette and view combinations', 'Define palette and view combinations for MetaModel ID %s');

/**
 * Reference
 */

$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_0'] = '0 Records are not sorted';
$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_1'] = '1 Records are sorted by a fixed field';
$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_2'] = '2 Records are sorted by a switchable field';
$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_3'] = '3 Records are sorted by the parent table';
$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_4'] = '4 Displays the child records of a parent record (see style sheets module)';
$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_5'] = '5 Records are displayed as tree (see site structure)';
$GLOBALS['TL_LANG']['tl_metamodel']['modes']['mode_6'] = '6 Displays the child records within a tree structure (see articles module)';

$GLOBALS['TL_LANG']['tl_metamodel']['rendertypes']['standalone'] = 'Standalone';
$GLOBALS['TL_LANG']['tl_metamodel']['rendertypes']['ctable']     = 'As child table';

/**
 * Misc.
 */
$GLOBALS['TL_LANG']['tl_metamodel']['itemFormat'] = ' <span style="color:#b3b3b3;"><em>(%s %s)</em></span>';
$GLOBALS['TL_LANG']['tl_metamodel']['itemSingle'] = 'item';
$GLOBALS['TL_LANG']['tl_metamodel']['itemPlural'] = 'items';


?>