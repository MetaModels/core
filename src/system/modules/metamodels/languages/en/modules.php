<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Core
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
 * Back end modules
 */
$GLOBALS['TL_LANG']['MOD']['metamodels']         = array('MetaModels', 'The MetaModels extension allows you to create own data models.');

/**
 * Front end modules
 */
$GLOBALS['TL_LANG']['FMD']['metamodels']         = array('MetaModels', 'The MetaModels extension allows you to create own data models.');
$GLOBALS['TL_LANG']['FMD']['metamodel_list']     = array('MetaModel list', 'Adds a list of MetaModel items to the page.');

?>