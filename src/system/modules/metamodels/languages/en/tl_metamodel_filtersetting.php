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
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['fid']          = array('Parent collection', 'The collection of filter settings, this setting belongs to');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['type']         = array('Type', 'The type of this setting.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['enabled']      = array('Enabled', 'Enable this filter setting.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['attr_id']      = array('Attribute', 'Attribute this setting relates to.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['all_langs']    = array('Search all languages', 'Check if you want to perform the lookup language independant. If this is not checked, only the current active language will be searched.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['allow_empty']  = array('Allow empty value', 'Check if you want to allow this filter value to be emtpy, if checked and the parameter holds an empty value, this filter rule will behave as if it was not defined.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['stop_after_match']  = array('Stop after first match', 'Check if you want the this filter setting to stop executing its child rules after the first subset found some matches.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['urlparam']     = array('URL parameter', 'The URL parameter that shall get mapped to the selected attribute. The special <em>"auto_item"</em> parameter can also be used, this is especially useful for alias columns.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['predef_param'] = array('Static parameter', 'Check if you want to be able to set the value of this parameter in the parenting list (Module, Content-element, etc.).');

$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['customsql'] = array('Custom SQL Query', 'The SQL query that shall be executed, insert tags are supported.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['title_legend']         = 'Type';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['config_legend']         = 'Configuration';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['new']                  = array('New', 'Create new setting.');

$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['edit']                 = array('Edit setting', 'Edit filter setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['copy']                 = array('Copy filter setting definiton', 'Copy filter setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['delete']               = array('Delete filter setting', 'Delete filter setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['show']                 = array('Filter setting details', 'Show details of filter setting ID %s');


/**
 * Reference
 */

$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['idlist']       = 'Predefined set';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['simplelookup'] = 'Simple lookup';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['customsql']    = 'Custom SQL';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['conditionor']  = 'OR condition';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['conditionand'] = 'AND condition';

$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['_default_']    = '%s <strong>%s</strong> <em>[%s]</em>';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['simplelookup'] = '%s <strong>%s</strong><br /> on attribute <em>%s</em> (URL param: %s)';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['conditionor']  = '%s <strong>%s</strong><br /> items that are mentioned in any result.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['conditionand'] = '%s <strong>%s</strong><br /> items that are mentioned in all results.';
?>