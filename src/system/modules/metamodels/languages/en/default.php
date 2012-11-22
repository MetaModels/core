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
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Content elements
 */
$GLOBALS['TL_LANG']['CTE']['metamodels']  = 'MetaModel elements';
$GLOBALS['TL_LANG']['CTE']['metamodel_content'] = array('MetaModel list', 'Adds a list of MetaModel items to the article.');

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['editRecord']   = 'Edit filter setting %%s for filter "%s" in MetaModel "%s"';
$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['label']        = 'Filter "%s" in MetaModel "%s"';
$GLOBALS['TL_LANG']['MSC']['metamodel_edit_as_child']['label']        = 'Edit "%s" for Item %%s';
$GLOBALS['TL_LANG']['MSC']['sorting']                                 = 'Sorting';

// Stylepicker
$GLOBALS['TL_LANG']['MSC']['tl_class']['w50']                         = array('w50', 'Set the field width to 50% and float it (float:left).');
$GLOBALS['TL_LANG']['MSC']['tl_class']['clr']                         = array('clr', 'Clear all floats.');
$GLOBALS['TL_LANG']['MSC']['tl_class']['m12']                         = array('m12', 'Add a 12 pixel top margin to the element (used for single checkboxes).');
$GLOBALS['TL_LANG']['MSC']['tl_class']['wizard']                      = array('wizard', 'Shorten the input field so there is enough room for the wizard button (e.g. date picker fields).');
$GLOBALS['TL_LANG']['MSC']['tl_class']['long']                        = array('long', 'Make the text input field span two columns.');

// Panelpicker
$GLOBALS['TL_LANG']['MSC']['panelLayout']['search']                   = array('Search', '');
$GLOBALS['TL_LANG']['MSC']['panelLayout']['sort']                     = array('Sort', '');
$GLOBALS['TL_LANG']['MSC']['panelLayout']['filter']                   = array('Filter', '');
$GLOBALS['TL_LANG']['MSC']['panelLayout']['limit']                    = array('Limit', '');

/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['no_attribute_extension']                  = 'Please install at least one attribute extension! MetaModels without attributes do not make sense.';
$GLOBALS['TL_LANG']['ERR']['activate_extension']                      = 'Please activate required extension &quot;%s&quot; (%s)';
$GLOBALS['TL_LANG']['ERR']['install_extension']                       = 'Please install required extension &quot;%s&quot; (%s)';
$GLOBALS['TL_LANG']['ERR']['columnExists']							  = 'There is already an attribute with the given column name.';
$GLOBALS['TL_LANG']['ERR']['no_palette']                              = 'Attempt to access the MetaModel "%s" without palette for current user %s.';
$GLOBALS['TL_LANG']['ERR']['no_view']                                 = 'Attempt to access the MetaModel "%s" without view for user %s.';
$GLOBALS['TL_LANG']['ERR']['invalidTableName']						  = 'The table name is not validate.';
/***
 * Filter setting parameters.
 */
$GLOBALS['TL_LANG']['MSC']['metamodel_filtersettings_parameter']['simplelookup'] = array('Filter value for attribute &quot;%s&quot;', '');

?>