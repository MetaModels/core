<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_LANG']['tl_metamodel_dca']['name'][0]                     = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['name'][1]                     = 'Name of the input screen.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['tstamp'][0]                   = 'Revision date';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['tstamp'][1]                   = 'Date and time of the latest revision.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertype'][0]               = 'Integration';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertype'][1]               = 'Select the desired type of integration.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendermode'][0]               = 'Render mode';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendermode'][1]               = 'Select the desired render mode.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['ptable'][0]                   = 'Parent table name';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['ptable'][1]                   =
    'Name of the database table that shall be referred to as parent table.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['showColumns'][0]              = 'Use column based layout';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['showColumns'][1]              =
    'If selected a table header will be generated with column names.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendsection'][0]           = 'Backend section';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendsection'][1]           =
    'Select the desired backend section where you want the MetaModel appear. ' .
    'For models that shall be edited by end users, the "content" section most likely will be appropriate.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendicon'][0]              = 'Backend icon';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendicon'][1]              =
    'Select the desired backend icon. ' .
    'This icon will get used to draw an image in the left menu and on the top of the edit view in tree displays.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendcaption'][0]           = 'Backend caption';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backendcaption'][1]           =
    'The text you specify in here, will get used as the label and description text in the backend menu.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_langcode'][0]           = 'Language';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_langcode'][1]           = 'Select the languages you want to provide.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_label'][0]              = 'Label text';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_label'][1]              =
    'The text you specify in here, will get used as the menu label in the backend menu.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_description'][0]        = 'Description text';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['becap_description'][1]        =
    'The text you specify in here, will get used as the description (hover title) in the backend menu.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['panelLayout'][0]              = 'Panel layout';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['panelLayout'][1]              =
    'Separate panel options with comma (= space) and semicolon (= new line) like sort,filter;search,limit.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['panelpicker']                 = 'Panelpicker';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['use_limitview'][0]            = 'View limitation';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['use_limitview'][1]            = 'Activate the view limitation.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['limit_rendersetting'][0]      = 'Limit the render setting';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['limit_rendersetting'][1]      = 'Choose between front end or backend.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['iseditable'][0]               = 'Allow editing of items';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['iseditable'][1]               =
    'If checked, this input screen allows the editing of items.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['iscreatable'][0]              = 'Allow creating of items';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['iscreatable'][1]              =
    'If checked, this input screen allows the creating of items.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['isdeleteable'][0]             = 'Allow deleting of items';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['isdeleteable'][1]             =
    'If checked, this input screen allows the deleting of items.';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['title_legend']                = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['view_legend']                 = 'View settings';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backend_legend']              = 'Backend integration';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['display_legend']              = 'Data display settings';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['permissions_legend']          = 'Data manipulation permissions';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['new'][0]                      = 'New input screen';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['new'][1]                      = 'Create new input screen';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['edit'][0]                     = 'Edit input screen';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['edit'][1]                     = 'Edit the input screen ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['copy'][0]                     = 'Copy input screen definition';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['copy'][1]                     = 'Copy definition of input screen ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['delete'][0]                   = 'Delete input screen';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['delete'][1]                   = 'Delete input screen ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['show'][0]                     = 'Input screen details';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['show'][1]                     = 'Show details of input screen ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['settings'][0]                 = 'Input screen settings';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['settings'][1]                 = 'Edit the settings of input screen ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['groupsort_settings'][0]       = 'Grouping and sorting';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['groupsort_settings'][1]       =
    'Edit the grouping and sorting settings of input screen ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertypes']['standalone']   = 'Standalone';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendertypes']['ctable']       = 'As child table';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendermodes']['flat']         = 'Flat';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendermodes']['parented']     = 'Parented';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['rendermodes']['hierarchical'] = 'Hierarchical';
