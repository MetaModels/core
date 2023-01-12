<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['name'][0]                      = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['name'][1]                      = 'Name of the sorting group.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['tstamp'][0]                    = 'Revision date';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['tstamp'][1]                    =
    'Date and time of the latest revision.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['isdefault'][0]                 = 'Is default';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['isdefault'][1]                 =
    'Determines that this input screen shall be used as default for the parenting MetaModel.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendermode'][0]                = 'Render mode';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendermode'][1]                = 'Select the desired render mode.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['becap_langcode'][0]            = 'Language';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['becap_langcode'][1]            =
    'Select the languages you want to provide.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['becap_label'][0]               = 'Label text';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['becap_label'][1]               =
    'The text you specify in here, will get used as the menu label in the backend menu.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['becap_description'][0]         = 'Description text';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['becap_description'][1]         =
    'The text you specify in here, will get used as the description (hover title) in the backend menu.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptype'][0]           = 'Grouping type';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptype'][1]           =
    'The grouping type to use in the item view.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergroupattr'][0]           = 'Grouping attribute';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergroupattr'][1]           =
    'The attribute to use for grouping in the item view.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouplen'][0]            = 'Grouping length';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouplen'][1]            =
    'The amount of characters to use for grouping.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersortattr'][0]            = 'Sorting attribute';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersortattr'][1]            = 'The attribute to sort by.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersort'][0]                = 'Sorting direction';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersort'][1]                = 'The sorting direction.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['ismanualsort'][0]              = 'Enable manual sorting';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['ismanualsort'][1]              =
    'If this is enabled, the user will be able to perform manual sorting.';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['title_legend']                 = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['display_legend']               = 'Data display settings';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['new'][0]                       = 'New definition';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['new'][1]                       = 'Create new definition';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['edit'][0]                      = 'Edit definition';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['edit'][1]                      = 'Edit the definition ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['copy'][0]                      = 'Copy definition';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['copy'][1]                      = 'Copy the definition ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['delete'][0]                    = 'Delete definition';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['delete'][1]                    = 'Delete the definition ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['show'][0]                      = 'Definition details';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['show'][1]                      = 'Show details of definition ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['toggle'][0]                    = 'Toggle';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['toggle'][1]                    =
    'Toggle the state of definition ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['settings'][0]                  = 'Definition settings';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['settings'][1]                  =
    'Edit the settings of definition ID %s';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['none']     = 'Do not group ';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['char']     = 'Group by initial letter(s)';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['digit']    = 'Group by numeric order';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['day']      = 'Group by day of date';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['weekday']  = 'Group by weekday of date';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['week']     = 'Group by week of year';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['month']    = 'Group by month of date';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['year']     = 'Group by year of date';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersortdirections']['asc']  = 'Ascending';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendersortdirections']['desc'] = 'Descending';

$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['deleteConfirm'] = 'Do you really want to delete definition ID %s?';
