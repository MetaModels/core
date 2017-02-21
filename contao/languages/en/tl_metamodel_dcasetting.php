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
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

/**
 * Fields
 */

$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatype']            = array('Type', 'Select the attribute type.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['attr_id']            = array('Attribute', 'Attribute this setting relates to.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['template']           = array('Custom template to use for generating', 'Select the template that shall be used for the selected attribute. Valid template files start with "mm_&lt;type&gt;" where the type name is put for &lt;type&gt;');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['tl_class']           = array('Backend class', 'Here you can set backend class(es). Use the style picker for a better experience.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['stylepicker']        = 'Style picker';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendhide']         = array('Hide legend', 'Hide the legend on default.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendtitle']        = array('Legend title', 'Here you can enter the legend title.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_langcode']      = 'Language';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_value']         = 'Legend title';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['mandatory']          = array('Mandatory', 'Check if this attribute shall be mandatory.
<br />NOTE: This will be implicitely active on if you selected "Unique values" in the attribute configuration.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['alwaysSave']         = array('Always save', 'If true the field will always be saved, even if its value has not changed.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['chosen']             = array('Chosen', 'Enable Chosen graphical select widget.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['filterable']         = array('Filterable', 'Check if this attribute shall be available for backend filtering.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['searchable']         = array('Searchable', 'Check if this attribute shall be available for backend search.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['allowHtml']          = array('Do not strip html content.', 'If you select this, HTML content will be preserved.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['preserveTags']       = array('Do not encode html tags.', 'If you select this, HTML tags will not be encoded.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['decodeEntities']     = array('Decode HTML entities.', 'Select this, if you want HTML Entities to be decoded.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['rte']                = array('Enable richtext editor on this', 'Select the rich text configuration that shall be used on this field (if any).');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['rows']               = array('Rows', 'Amount of rows to use for longtext/table widget.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['cols']               = array('Columns', 'Amount of colums to use for longtext/table widget');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash']      = array('Trailing slash handling', 'Here you can specify how trailing slashes shall be handled');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['spaceToUnderscore']  = array('Replace spaces with underscore', 'If true any whitespace character will be replaced by an underscore.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['includeBlankOption'] = array('Include blank option', 'if true a blank option will be added to the options which allows to define a &quot;no item selected&quot; option.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['readonly']           = array('Read only', 'If true a the widget will be read only and may not be changed.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['submitOnChange']     = array('Submit on change', 'If active the form will be submitted when the field value changes.');



/**
 * Legends
 */

$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['title_legend']        = 'Type';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['presentation_legend'] = 'Widget appearance related options';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['functions_legend']    = 'Functionality related options';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['overview_legend']     = 'Backend listing, filtering and sorting';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['backend_legend']      = 'Backend';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['config_legend']       = 'Configuration';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['advanced_legend']     = 'Advanced';

/**
 * Buttons
 */

$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['new']             = array('New', 'Create new setting.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['edit']            = array('Edit setting', 'Edit setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['copy']            = array('Copy setting definition', 'Copy setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['delete']          = array('Delete setting', 'Delete setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['conditions']      = array('Manage visibility conditions', 'Manage the visibility conditions of property ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['show']            = array('Setting details', 'Show details of setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall']          = array('Add all', 'Add all attributes to input screen');

/**
 * References
 */

$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatypes']['legend']         = 'Legend';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatypes']['attribute']      = 'Attribute';

/**
 * Reference
 */

$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash_options']['0'] = 'Strip slash on save';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash_options']['1'] = 'Add slash on save';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash_options']['2'] = 'Do nothing';

/**
 * Messages
 */

$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_willadd']               = 'Will add the attribute "%s" to the input screen.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_alreadycontained']      = 'Attribute %s is already contained in input screen.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_addsuccess']            = 'Added the attribute "%s" to the input screen.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['mandatory_for_unique_attr']    = 'Input screen settings for unique attribues are automatically mandatory (this is not changable).';
