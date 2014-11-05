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
 * @author     Tim Becker <please.tim@metamodel.me>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['name']                = array(
    'Name',
    'Name of the searchable page setting'
);
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['tstamp']              = array(
    'Revision date',
    'Date and time of the latest revision.'
);
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['isdefault']           = array(
    'Is default',
    'Determines that this searchable page setting shall be used as default for the parenting MetaModel.'
);
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['becap_description']   = array(
    'Description text',
    'The text you specify in here, will get used as the description (hover title) in the backend menu.'
);
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['setRendersetting']       = array(
    'Rendersetting',
    'Choose the rendersetting which will be used for the search rendering.'
);
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['setFilter']       = array(
    'Filtersetting',
    'Choose the filtersetting which will be used for the search rendering.'
);


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['title_legend']   = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['general_legend'] = 'General settings';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['new']      = array(
    'New searchable page',
    'Create new searchable page setting'
);
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['edit']     = array(
    'Edit searchable page',
    'Edit the searchable page setting ID %s'
);
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['copy']     = array(
    'Copy searchable page',
    'Copy definition of searchable page setting ID %s'
);
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['delete']   = array(
    'Delete searchable page',
    'Delete searchable page setting ID %s'
);
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['show']     = array(
    'Searchable page setting details',
    'Show details of searchable page setting ID %s'
);
$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['settings'] = array(
    'Ssearchable page settings',
    'Edit the settings of searchable page setting ID %s'
);