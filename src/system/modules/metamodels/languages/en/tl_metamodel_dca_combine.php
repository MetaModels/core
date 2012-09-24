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
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_combiner']        = array('Permissions for palettes and views', 'For selected frontend user group (if any) and selected backend user group (if any) use the selected palette and the selected view.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['fe_group']            = array('FE group', 'The frontend user group the combination applies to.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['be_group']            = array('BE group', 'The backend user group the combination applies to.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['dca_id']              = array('The palette', 'The palette the combination applies to.');
$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['view_id']             = array('The render setting', 'The view the combination applies to.');

$GLOBALS['TL_LANG']['tl_metamodel_dca_combine']['sysadmin']            = 'Sys. admins';

?>