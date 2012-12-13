<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_module']['mm_filter_legend']				= 'MetaModel Filter';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['metamodel']                = array('MetaModel', 'The MetaModel to list in this listing.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_use_limit']      = array('Use offset and limit for listing', 'Check if you want to limit the amount of items listed. This is useful for only showing the first 500 items or all excluding the first 10 items but keep pagination intact.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_offset']         = array('List offset', 'Please specify the offset value (i.e. 10 to skip the first 10 items).');
$GLOBALS['TL_LANG']['tl_module']['metamodel_limit']          = array('Maximum number of items', 'Please enter the maximum number of items. Enter 0 to show all items and therefore disable the pagination.');

$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby']         = array('Order by', 'Please choose the sort order.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_sortby_direction'] = array('Order by direction', 'Ascending or descending order.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_filtering']      = array('Filter settings to apply', 'Select the filter settings that shall get applied when compiling the list.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_layout']         = array('Custom template to use for generating', 'Select the template that shall be used for the selected attribute. Valid template filenames start with "mod_metamodel_&lt;type&gt;" where the module type name is put for &lt;type&gt;');
$GLOBALS['TL_LANG']['tl_module']['metamodel_rendersettings'] = array('Render settings to apply', 'Select the rendering settings to use for generating the output. If left empty, the default settings for the selected MetaModel will get applied. If no default has been defined, the output will only get the raw values.');
$GLOBALS['TL_LANG']['tl_module']['metamodel_filterparams']			= array('Filter parameters to use');
$GLOBALS['TL_LANG']['tl_module']['metamodel_filterparams_use_get'] = array('Use GET Param', 'allow this parameter to be overridden from _GET');


$GLOBALS['TL_LANG']['tl_module']['metamodel_noparsing']      = array('No parsing of items', 'If this checkbox is selected, the module will not parse the items. Only the item-objects will be available in the template.');
/**
 * Wizards
 */

$GLOBALS['TL_LANG']['tl_module']['editmetamodel']            = array('Edit MetaModel', 'Edit the MetaModel ID %s.');
$GLOBALS['TL_LANG']['tl_module']['editrendersetting']        = array('Edit render setting', 'Edit the render setting ID %s.');
$GLOBALS['TL_LANG']['tl_module']['editfiltersetting']        = array('Edit filter setting', 'Edit the filter setting ID %s.');


?>