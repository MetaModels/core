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
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

require_once(TL_ROOT . '/system/modules/metamodels/metamodel_functions.php');

/**
 * Back-end module
 */
// restrict database queries for active metamodels to the backend.
if (TL_MODE=='BE')
{
	MetaModelFactory::buildBackendMenu();
}

/**
 * Front-end modules
 */

$GLOBALS['FE_MOD']['metamodels'] = array
	(
		'metamodel_list'			=> 'ModuleMetaModelList',
	);




/*
$GLOBALS['METAMODELS']['attributes']['alias'] = array
(
	'class' => 'MetaModelAttritbuteAlias',
	'image' => ''
);
*/

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('MetaModelDatabase', 'createDataContainer');

?>