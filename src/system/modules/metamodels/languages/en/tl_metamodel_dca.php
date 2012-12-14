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
$GLOBALS['TL_LANG']['tl_metamodel_dca']['name']                 = array('Name', 'Name of the palette.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['tstamp']               = array('Revision date', 'Date and time of the latest revision.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['isdefault']            = array('Is default', 'Determines that this palette shall be used as default for the parenting MetaModel.');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertype']           = array('Integration', 'Select the desired type of integration.');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['ptable']               = array('Parent table name (if any)', 'Name of the database table that shall be referred to as parent table.');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['mode']                 = array('Sorting mode', 'The sorting mode to use in the item view.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['flag']                 = array('Sorting flag', 'The sorting flag to use in the item view.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendsection']       = array('Back end section', 'Select the desired back end section where you want the MetaModel appear. For models that shall be edited by end users, the "content" section most likely will be appropriate.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendicon']          = array('Back end icon', 'Select the desired back end icon. This icon will get used to draw an image in the left menu and on the top of the edit view in tree displays.');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendcaption']       = array('Back end caption', 'The text you specify in here, will get used as the label and description text in the back end menu.');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_langcode']       = array('Language', 'Select the languages you want to provide.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_label']          = array('Label text', 'The text you specify in here, will get used as the menu label in the back end menu.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_description']    = array('Description text', 'The text you specify in here, will get used as the description (hover title) in the back end menu.');


$GLOBALS['TL_LANG']['tl_metamodel_dca']['panelLayout']          = array('Panel layout', 'Separate panel options with comma (= space) and semicolon (= new line) like sort,filter;search,limit.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['panelpicker']          = 'Panelpicker';

$GLOBALS['TL_LANG']['tl_metamodel_dca']['use_limitview']        = array('View limitation', 'Activate the view limitation.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['limit_rendersetting']  = array('Limit the render setting', 'Choose between front end or back end.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['title_legend']         = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['view_legend']          = 'View settings';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backend_legend']       = 'Back end integration';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['new']                  = array('New palette', 'Create new palette');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['edit']                 = array('Edit palette', 'Edit the palette ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['copy']                 = array('Copy palette definition', 'Copy definition of palette ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['delete']               = array('Delete palette', 'Delete palette ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['show']                 = array('Palette details', 'Show details of palette ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['settings']             = array('Palette settings', 'Edit the settings of palette ID %s');

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

$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_0'] = '0 Records are not sorted';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_1'] = '1 Records are sorted by a fixed field';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_2'] = '2 Records are sorted by a switchable field';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_3'] = '3 Records are sorted by the parent table';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_4'] = '4 Displays the child records of a parent record (see style sheets module)';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_5'] = '5 Records are displayed as tree (see site structure)';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['sortingmode']['mode_6'] = '6 Displays the child records within a tree structure (see articles module)';

$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertypes']['standalone'] = 'Standalone';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertypes']['ctable']     = 'As child table';

