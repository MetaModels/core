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
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatype']            = array('Type', 'Select the attribute type.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['attr_id']            = array('Attribute', 'Attribute this setting relates to.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['template']           = array('Custom template to use for generating', 'Select the template that shall be used for the selected attribute. Valid template files start with "mm_&lt;type&gt;" where the type name is put for &lt;type&gt;');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['tl_class']           = array('Backend class', 'Here you can set backend class(es). Use the style picker for a better experience.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['stylepicker']        = 'Style picker';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendhide']         = array('Hide legend', 'Hide the legend on default.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendtitle']        = array('Legend title', 'Here you can enter the legend title.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_langcode']      = 'Language';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_value']         = 'Legend title';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['mandatory']          = array('Mandatory', 'Check if this attribute shall be mandatory.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['alwaysSave']         = array('Always save', 'If true the field will always be saved, even if its value has not changed.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['chosen']             = array('Chosen', 'Enable Chosen graphical select widget.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['filterable']         = array('Filterable', 'Check if this attribute shall be available for backend filtering.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortable']           = array('Sortable', 'Check if this attribute shall be available for backend sorting.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['searchable']         = array('Searchable', 'Check if this attribute shall be available for backend search.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['flag']               = array('Sorting flag override', 'If you want to override the global sorting flag from the palette for this attribute, please select it here.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['allowHtml']          = array('Do not strip html content.', 'If you select this, HTML content will be preseved.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['preserveTags']       = array('Do not encode html tags.', 'If you select this, HTML tags will not be encoded.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['decodeEntities']     = array('Decode HTML entities.', 'Select this, if you want HTML Entities to be decoded.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['rte']                = array('Enable richtext editor on this', 'Select the rich text configuration that shall be used on this field (if any).');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['rows']               = array('Rows', 'Amount of rows to use for longtext/table widget.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['cols']               = array('Columns', 'Amount of colums to use for longtext/table widget');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash']      = array('Trailing slash handling', 'Here you can specify how trailing slashes shall be handled');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['spaceToUnderscore']  = array('Replace spaces with underscore', 'If true any whitespace character will be replaced by an underscore.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['includeBlankOption'] = array('Include blank option', 'if true a blank option will be added to the options which allows to define a &quot;no item selected&quot; option.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['title_legend']    = 'Type';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['backend_legend']  = 'Backend';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['config_legend']   = 'Configuration';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['advanced_legend'] = 'Advanced';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['new']             = array('New', 'Create new setting.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['edit']            = array('Edit setting', 'Edit setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['copy']            = array('Copy setting definition', 'Copy setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['delete']          = array('Delete setting', 'Delete setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['subpalette']      = array('Manage sub areas', 'Manage the sub areas of input screen ID %s');
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
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['1']     = 'Sort by initial letter ascending';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['2']     = 'Sort by initial letter descending';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['3']     = 'Sort by initial "length" letters ascending';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['4']     = 'Sort by initial "length" letters descending';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['5']     = 'Sort by day ascending';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['6']     = 'Sort by day descending';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['7']     = 'Sort by month ascending';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['8']     = 'Sort by month descending';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['9']     = 'Sort by year ascending';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['10']    = 'Sort by year descending';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['11']    = 'Sort ascending';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortingflag']['12']    = 'Sort descending';


$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash_options']['0'] = 'Strip slash on save';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash_options']['1'] = 'Add slash on save';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash_options']['2'] = 'Do nothing';

/**
 * Messages
 */

$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_willadd']               = 'Will add the attribute "%s" to the input screen.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_alreadycontained']      = 'Attribute %s is already contained in input screen.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_addsuccess']            = 'Added the attribute "%s" to the input screen.';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_addsuccess_subpalette'] = 'Added the attribute "%s" to sub area "%s".';
