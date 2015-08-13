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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['name']                 = array('Name', 'Name of the input screen.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['tstamp']               = array('Revision date', 'Date and time of the latest revision.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['isdefault']            = array('Is default', 'Determines that this input screen shall be used as default for the parenting MetaModel.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertype']           = array('Integration', 'Select the desired type of integration.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendermode']           = array('Render mode', 'Select the desired render mode.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['ptable']               = array('Parent table name', 'Name of the database table that shall be referred to as parent table.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendsection']       = array('Backend section', 'Select the desired backend section where you want the MetaModel appear. For models that shall be edited by end users, the "content" section most likely will be appropriate.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendicon']          = array('Backend icon', 'Select the desired backend icon. This icon will get used to draw an image in the left menu and on the top of the edit view in tree displays.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendcaption']       = array('Backend caption', 'The text you specify in here, will get used as the label and description text in the backend menu.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_langcode']       = array('Language', 'Select the languages you want to provide.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_label']          = array('Label text', 'The text you specify in here, will get used as the menu label in the backend menu.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_description']    = array('Description text', 'The text you specify in here, will get used as the description (hover title) in the backend menu.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['panelLayout']          = array('Panel layout', 'Separate panel options with comma (= space) and semicolon (= new line) like sort,filter;search,limit.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['panelpicker']          = 'Panelpicker';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['use_limitview']        = array('View limitation', 'Activate the view limitation.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['limit_rendersetting']  = array('Limit the render setting', 'Choose between front end or backend.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['iseditable']           = array('Allow editing of items', 'If checked, this input screen allows the editing of items.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['iscreatable']          = array('Allow creating of items', 'If checked, this input screen allows the creating of items.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['isdeleteable']         = array('Allow deleting of items', 'If checked, this input screen allows the deleting of items.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['title_legend']         = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['view_legend']          = 'View settings';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backend_legend']       = 'Backend integration';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['display_legend']       = 'Data display settings';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['permissions_legend']   = 'Data manipulation permissions';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['new']                  = array('New input screen', 'Create new input screen');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['edit']                 = array('Edit input screen', 'Edit the input screen ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['copy']                 = array('Copy input screen definition', 'Copy definition of input screen ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['delete']               = array('Delete input screen', 'Delete input screen ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['show']                 = array('Input screen details', 'Show details of input screen ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['settings']             = array('Input screen settings', 'Edit the settings of input screen ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['groupsort_settings']   = array('Grouping and sorting', 'Edit the grouping and sorting settings of input screen ID %s');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertypes']['standalone'] = 'Standalone';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertypes']['ctable']     = 'As child table';

$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendermodes']['flat']         = 'Flat';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendermodes']['parented']     = 'Parented';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendermodes']['hierarchical'] = 'Hierarchical';
