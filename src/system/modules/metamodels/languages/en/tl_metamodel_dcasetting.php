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
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatype']         = array('Type', 'Select the attribute type.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['attr_id']         = array('Attribute', 'Attribute this setting relates to.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['template']        = array('Custom Template to use for generating', 'Select the template that shall be used for the selected attribute. Valid template files start with &quot;mm_<type>&quot; where the type name is put for &lt;type&gt;');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['tl_class']        = array('Backend class', 'Here you can set backend class(es). Use the stylepicker for a better experience.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['stylepicker']     = 'Stylepicker';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendhide']      = array('Hide legend','Hide the legend on default.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendtitle']     = array('Legend title','Here you can enter the legend title.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_langcode']   = 'Language';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_value']      = 'Legend title';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['mandatory']       = array('Mandatory', 'Check if this attribute shall be mandatory.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['filterable']      = array('Filterable', 'Check if this attribute shall be available for backend filtering.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['sortable']        = array('Sortable', 'Check if this attribute shall be available for backend sorting.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['searchable']      = array('Searchable', 'Check if this attribute shall be available for backend search.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['flag']            = array('Sorting flag override', 'If you want to override the global sorting flag from the palette for this attribute, please select it here.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['title_legend']    = 'Type';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['advanced_legend'] = 'Advanced';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['new']             = array('New', 'Create new setting.');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['edit']            = array('Edit setting', 'Edit setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['copy']            = array('Copy setting definiton', 'Copy setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['delete']          = array('Delete setting', 'Delete setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['show']            = array('Setting details', 'Show details of setting ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall']          = array('Add all', 'Add all attributes to palette');

$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['row']             = '%s <strong>%s</strong> <em>[%s]</em>';
$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legend_row']      = '<div class="dca_palette">%s%s</div>';

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


?>