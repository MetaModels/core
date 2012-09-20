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
$GLOBALS['TL_LANG']['tl_metamodel_dca']['name']                 = array('Name', 'DCA name.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['tstamp']               = array('Revision date', 'Date and time of the latest revision');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['be_template']          = array('Backend template', 'The template to use in the backend when listing items.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['be_groups']            = array('Backend user groups', 'Select any user group that may edit this MetaModel in the backend.');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['fe_groups']            = array('Frontend user groups', 'Select any user group that may edit this MetaModel in the frontend.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['title_legend']         = 'Name';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['backend_legend']       = 'Backend integration';
$GLOBALS['TL_LANG']['tl_metamodel_dca']['permission_legend']    = 'Permission settings';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_dca']['new']                  = array('New DCA', 'Create new DCA.');

$GLOBALS['TL_LANG']['tl_metamodel_dca']['edit']                 = array('Edit DCA', 'Edit the DCA ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['copy']                 = array('Copy DCA definiton', 'Copy definition of DCA ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['delete']               = array('Delete DCA', 'Delete DCA ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['show']                 = array('DCA details', 'Show details of DCA ID %s');
$GLOBALS['TL_LANG']['tl_metamodel_dca']['settings']             = array('DCA Palette', 'Edit the palette of DCA ID %s');

/**
 * Reference
 */

?>