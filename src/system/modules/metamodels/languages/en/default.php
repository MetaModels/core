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
 * Content elements
 */
$GLOBALS['TL_LANG']['CTE']['metamodels']                              = 'MetaModel elements';
$GLOBALS['TL_LANG']['CTE']['metamodel_content']                       = array('MetaModel list', 'Adds a list of MetaModel items to the article.');
$GLOBALS['TL_LANG']['CTE']['metamodels_frontendfilter']               = array('MetaModel frontend filter','Adds a frontend filter for a MetaModel.');
$GLOBALS['TL_LANG']['CTE']['metamodels_frontendclearall']             = array('MetaModel clear all', 'Adds a clear all for all frontend filter.');

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['editRecord']   = 'Edit filter setting %%s for filter "%s" in MetaModel "%s"';
$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['label']        = 'Filter "%s" in MetaModel "%s"';
$GLOBALS['TL_LANG']['MSC']['metamodel_rendersetting']['editRecord']   = 'Edit attribute setting %%s for render setting "%s" in MetaModel "%s"';
$GLOBALS['TL_LANG']['MSC']['metamodel_rendersetting']['label']        = 'Render setting "%s" in MetaModel "%s"';

$GLOBALS['TL_LANG']['BRD']['metamodels']                              = 'MetaModels';
$GLOBALS['TL_LANG']['BRD']['metamodel_attribute']                     = 'Attributes of "%s"';
$GLOBALS['TL_LANG']['BRD']['metamodel_rendersettings']                = 'All render setting of "%s"';
$GLOBALS['TL_LANG']['BRD']['metamodel_rendersetting']                 = 'Render settings in "%s"';
$GLOBALS['TL_LANG']['BRD']['metamodel_dca']                           = 'All input screens of "%s"';
$GLOBALS['TL_LANG']['BRD']['metamodel_dcasetting']                    = 'Input screens in "%s"';
$GLOBALS['TL_LANG']['BRD']['metamodel_dcasetting_subpalette']         = 'Sub areas for "%s"';
$GLOBALS['TL_LANG']['BRD']['metamodel_filter']                        = 'All filter of "%s"';
$GLOBALS['TL_LANG']['BRD']['metamodel_filtersetting']                 = 'Filter settings in "%s"';

$GLOBALS['TL_LANG']['MSC']['metamodel_edit_as_child']['label']        = 'Edit "%s" for item %%s';
$GLOBALS['TL_LANG']['MSC']['sorting']                                 = 'Sorting';
$GLOBALS['TL_LANG']['MSC']['template_in_theme']                       = '%s (%s)';
$GLOBALS['TL_LANG']['MSC']['no_theme']                                = 'global scope';
$GLOBALS['TL_LANG']['MSC']['noItemsMsg']                              = 'There are no items matching your search.';
$GLOBALS['TL_LANG']['MSC']['details']                                 = 'Details';
$GLOBALS['TL_LANG']['MSC']['field_label']                             = '%s :';

// Stylepicker
$GLOBALS['TL_LANG']['MSC']['tl_class']['w50']                         = array('w50', 'Set the field width to 50% and float it (float:left).');
$GLOBALS['TL_LANG']['MSC']['tl_class']['w50x']                        = array('w50x', 'Remove only the annoying fixed height, please use it together with "w50".');
$GLOBALS['TL_LANG']['MSC']['tl_class']['clr']                         = array('clr', 'Clear all floats.');
$GLOBALS['TL_LANG']['MSC']['tl_class']['clx']                         = array('clx', 'Remove only the annoying overflow hidden, please use it together with "clr".');
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
$GLOBALS['TL_LANG']['ERR']['activate_extension']                      = 'Please activate the required extension "%s" (%s)';
$GLOBALS['TL_LANG']['ERR']['install_extension']                       = 'Please install the required extension "%s" (%s)';
$GLOBALS['TL_LANG']['ERR']['columnExists']                            = 'There is already an attribute with the given column name.';
$GLOBALS['TL_LANG']['ERR']['no_palette']                              = 'Attempt to access the MetaModel "%s" without input screen for current user %s.';
$GLOBALS['TL_LANG']['ERR']['no_view']                                 = 'Attempt to access the MetaModel "%s" without view for user %s.';
$GLOBALS['TL_LANG']['ERR']['invalidTableName']                        = 'The table name is invalid.';
$GLOBALS['TL_LANG']['ERR']['upgrade_php_version']                     = 'The version of the PHP interpreter is too low, please upgrade to at least %s (you are currently running %s)';
$GLOBALS['TL_LANG']['ERR']['invalidTableName']                        = 'The table name "%s" is invalid.';
$GLOBALS['TL_LANG']['ERR']['invalidColumnName']                       = 'The column name "%s" is invalid.';
$GLOBALS['TL_LANG']['ERR']['systemColumn']                            = 'The column name "%s" is reserved for system use.';
$GLOBALS['TL_LANG']['ERR']['tableDoesNotExist']                       = 'Table "%s" does not exist.';
$GLOBALS['TL_LANG']['ERR']['tableExists']                             = 'Table "%s" already exists.';
$GLOBALS['TL_LANG']['ERR']['columnDoesNotExist']                      = 'Column "%s" does not exist on table "%s".';
$GLOBALS['TL_LANG']['ERR']['columnExists']                            = 'Column "%s" already exists on table "%s".';

/**
 * Labels
 */
$GLOBALS['TL_LANG']['metamodels_frontendfilter']['submit']            = 'Filter';
$GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter']     = 'No filtering';
$GLOBALS['TL_LANG']['metamodels_frontendfilter']['select_all']        = 'Select all';
$GLOBALS['TL_LANG']['metamodels_frontendfilter']['clear_all']         = 'Clear all filter';
$GLOBALS['TL_LANG']['metamodels_frontendfilter']['action_add']        = '+';
$GLOBALS['TL_LANG']['metamodels_frontendfilter']['action_remove']     = '-';
$GLOBALS['TL_LANG']['metamodels_frontendfilter']['no_combinations']   = ' (No matching combinations found.)';

/**
 * Filter setting parameters.
 */
$GLOBALS['TL_LANG']['MSC']['metamodel_filtersettings_parameter']['simplelookup'] = array('Filter value for attribute "%s"', '');

/**
 * Support
 */
$GLOBALS['TL_LANG']['MSC']['metamodels_help']                         = 'We are calling for your help!';
$GLOBALS['TL_LANG']['MSC']['metamodels_contributor']                  = 'Thanks to these users for tickets, suggestions and translations';