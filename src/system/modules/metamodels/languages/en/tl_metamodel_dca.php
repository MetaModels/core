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
$GLOBALS['TL_LANG']['tl_metamodel_dca']['name']                 = array('Name', 'Name of the palette.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['tstamp']               = array('Revision date', 'Date and time of the latest revision');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['isdefault']            = array('Is default', 'Determines that this palette shall be used as default for the parenting MetaModel');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['mode']                 = array('Sorting mode', 'The sorting mode to use in the item view.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['flag']                 = array('Sorting flag', 'The sorting flag to use in the item view.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['panelLayout']          = array('Panel layout', 'Separate panel options with comma (= space) and semicolon (= new line) like sort,filter;search,limit.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['panelpicker']          = 'Panelpicker';

$GLOBALS['TL_LANG']['tl_metamodel_dca']['fields']               = array('Field configuration', 'Configure the header fields for a better user experience.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['field_attribute']      = 'Attribute name';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['field_filterable']     = 'Filterable';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['field_sortable']       = 'Sortable';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['field_searchable']     = 'Searchable';

$GLOBALS['TL_LANG']['tl_metamodel_dca']['use_limitview']        = array('View limitation', 'Activate the view limitation.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['limit_rendersetting']  = array('Limit the rendersetting', 'Choose between frontend or backend.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['title_legend']         = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['view_legend']          = 'View settings';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['new']                  = array('New palette', 'Create new palette');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['edit']                 = array('Edit palette', 'Edit the palette ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['copy']                 = array('Copy palette definiton', 'Copy definition of palette ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['delete']               = array('Delete palette', 'Delete palette ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['show']                 = array('Palette details', 'Show details of palette ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['settings']             = array('Palette setting', 'Edit the setting of palette ID %s');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['1']     = 'Sort by initial letter ascending';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['2']     = 'Sort by initial letter descending';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['3']     = 'Sort by initial two letters ascending';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['4']     = 'Sort by initial two letters descending';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['5']     = 'Sort by day ascending';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['6']     = 'Sort by day descending';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['7']     = 'Sort by month ascending';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['8']     = 'Sort by month descending';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['9']     = 'Sort by year ascending';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['10']    = 'Sort by year descending';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['11']    = 'Sort ascending';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingflag']['12']    = 'Sort descending';

$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['1']     = 'Records are sorted by a fixed field';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['2']     = 'Records are sorted by a switchable field';

?>