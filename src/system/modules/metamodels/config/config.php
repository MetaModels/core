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


/*
	In order to add attribute types into the system, add the following snippet to your extension config.php:

	$GLOBALS['METAMODELS']['attributes']['TYPENAME'] = array
	(
		'class' => 'TYPECLASS',
		'image' => 'IMAGEPATH',
		'factory' => 'FACTORYCLASS' // optional
	);

	where:
		TYPENAME     is the internal type name of your attribute.
		TYPECLASS    is the name of the implementing class.
		IMAGEPATH    path to an icon (16x16) that represents the attribute type. Based from TL_ROOT.
		FACTORYCLASS this is optional, if defined, the herein declared classname will be used for instantiation
		             of attributes of this type instead of plain constructing.
*/


// define our version so dependant extensions can use it in version_compare().
define('METAMODELS_VERSION', '0.1');

/**
 * Back-end module
 */
if (TL_MODE=='BE')
{
	// restrict to the backend.
	MetaModelFactory::buildBackendMenu();
}

/**
 * Front-end modules
 */

$GLOBALS['FE_MOD']['metamodels'] = array
	(
		'metamodel_list'			=> 'ModuleMetaModelList',
	);

/**
 * HOOKS
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('MetaModelDatabase', 'createDataContainer');

?>