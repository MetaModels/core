<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_content']['mm_config_legend']               = 'MetaModel Configuration';
$GLOBALS['TL_LANG']['tl_content']['mm_filter_legend']               = 'MetaModel Filter';
$GLOBALS['TL_LANG']['tl_content']['mm_rendering']                   = 'MetaModel Rendering';
$GLOBALS['TL_LANG']['tl_content']['mm_meta_legend']                 = 'Search engine optimization';

/**
 * Selects
 */
$GLOBALS['TL_LANG']['tl_content']['ASC']                            = 'Ascending';
$GLOBALS['TL_LANG']['tl_content']['DESC']                           = 'Descending';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_content']['metamodel']                      = array('MetaModel', 'The MetaModel to list in this listing.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_use_limit']            = array('Use offset and limit for listing', 'Check if you want to limit the amount of items listed. This is useful for only showing the first 500 items or all excluding the first 10 items but keep pagination intact.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_offset']               = array('List offset', 'Please specify the offset value (i.e. 10 to skip the first 10 items).');
$GLOBALS['TL_LANG']['tl_content']['metamodel_limit']                = array('Maximum number of items', 'Please enter the maximum number of items. Enter 0 to show all items and therefore disable the pagination.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_sortby']               = array('Order by', 'Please choose the sort order.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_sortby_direction']     = array('Order by direction', 'Ascending or descending order.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_filtering']            = array('Filter settings to apply', 'Select the filter settings that shall get applied when compiling the list.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_layout']               = array('Custom template to use for generating', 'Select the template that shall be used for the selected attribute. Valid template filenames start with "ce_metamodel".');
$GLOBALS['TL_LANG']['tl_content']['metamodel_rendersettings']       = array('Render settings to apply', 'Select the rendering settings to use for generating the output. If left empty, the default settings for the selected MetaModel will get applied. If no default has been defined, the output will only get the raw values.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_noparsing']            = array('No parsing of items', 'If this checkbox is selected, the module will not parse the items. Only the item objects will be available in the template.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_donotindex']           = array('Do not add to search index', 'If this is checked, the Contao internal search index will ignore the content of this content element.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_filterparams']         = array('Filter parameter override');
$GLOBALS['TL_LANG']['tl_content']['metamodel_filterparams_use_get'] = array('Use GET Parameter', '');
$GLOBALS['TL_LANG']['tl_content']['metamodel_jumpTo']               = array('Redirect page', 'Please choose the page to which visitors will be redirected when clicking a link or submitting a form.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_fef_params']           = array('Attributes', 'Select the attributes used in this frontend filter.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_fef_template']         = array('Template', 'Select frontend template.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_fef_autosubmit']       = array('Submit on change', 'Reload page on filter change.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_fef_hideclearfilter']  = array('Hide clear filter option', 'Hide the clear filter option in every filter.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_available_values']     = array('Filter counter', 'Display the amount of available entries behind each filter option.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_meta_title']           = array('Meta Title', 'Set this attribute as the meta-title of the page.');
$GLOBALS['TL_LANG']['tl_content']['metamodel_meta_description']     = array('Meta Description', 'Set this attribute as the meta-description of the page.');

/**
 * Wizards
 */
$GLOBALS['TL_LANG']['tl_content']['editmetamodel']                  = array('Edit MetaModel', 'Edit the MetaModel ID %s.');
$GLOBALS['TL_LANG']['tl_content']['editrendersetting']              = array('Edit rendersetting', 'Edit the render setting ID %s.');
$GLOBALS['TL_LANG']['tl_content']['editfiltersetting']              = array('Edit filtersetting', 'Edit the filter setting ID %s.');

