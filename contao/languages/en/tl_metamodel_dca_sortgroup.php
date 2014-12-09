<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['name']                        = array('Name', 'Name of the sorting group.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['tstamp']                      = array('Revision date', 'Date and time of the latest revision.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['isdefault']                   = array('Is default', 'Determines that this input screen shall be used as default for the parenting MetaModel.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendermode']                  = array('Render mode', 'Select the desired render mode.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['becap_langcode']              = array('Language', 'Select the languages you want to provide.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['becap_label']                 = array('Label text', 'The text you specify in here, will get used as the menu label in the backend menu.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['becap_description']           = array('Description text', 'The text you specify in here, will get used as the description (hover title) in the backend menu.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptype']             = array('Grouping type', 'The grouping type to use in the item view.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergroupattr']             = array('Grouping attribute', 'The attribute to use for grouping in the item view.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouplen']              = array('Grouping length', 'The amount of characters to use for grouping.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['ismanualsort']                = array('Enable manual sorting', 'If this is enabled, the user will be able to perform manual sorting.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['title_legend']                = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['display_legend']              = 'Data display settings';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['new']                         = array('New definition', 'Create new definition');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['edit']                        = array('Edit definition', 'Edit the definition ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['copy']                        = array('Copy definition', 'Copy definition ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['delete']                      = array('Delete definition', 'Delete definition ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['show']                        = array('Definition details', 'Show details of definition ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['settings']                    = array('Definition settings', 'Edit the settings of definition ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['none']    = 'Do not group ';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['char']    = 'Group by initial letter(s)';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['digit']   = 'Group by numeric order';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['day']     = 'Group by day of date';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['weekday'] = 'Group by weekday of date';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['week']    = 'Group by week of year';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['month']   = 'Group by month of date';
$GLOBALS['TL_LANG']['tl_metamodel_dca_sortgroup']['rendergrouptypes']['year']    = 'Group by year of date';
