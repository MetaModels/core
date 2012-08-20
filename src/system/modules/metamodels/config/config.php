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


/*
	In order to add filter rule types into the system, add the following snippet to your extension config.php:

	$GLOBALS['METAMODELS']['filters']['TYPENAME'] = array
	(
		'class' => 'TYPECLASS',
		'image' => 'IMAGEPATH',
		'info_callback' => array(CALLBACK_CLASS, CALLBACK_METHOD),
		'nestingAllowed' => NESTINGVALUE // optional
	);

	where:
		TYPENAME     is the internal type name of your filter setting.
		TYPECLASS    is the name of the implementing class.
		IMAGEPATH    path to an icon (16x16) that represents the filter rule type. Based from TL_ROOT.
		NESTINGVALUE boolean true or false. If this is true, you indicate that this rule may contain child rules.
*/

$GLOBALS['METAMODELS']['filters']['idlist'] = array
(
	'class' => 'MetaModelFilterSettingIdList',
);

$GLOBALS['METAMODELS']['filters']['simplelookup'] = array
(
	'class' => 'MetaModelFilterSettingSimpleLookup',
	'info_callback' => array('TableMetaModelFilterSetting', 'drawSimpleLookup')
);

$GLOBALS['METAMODELS']['filters']['customsql'] = array
(
	'class' => 'MetaModelFilterSettingCustomSQL',
	'image' => 'system/modules/metamodels/html/filter_customsql.png',
);

$GLOBALS['METAMODELS']['filters']['conditionand'] = array
(
	'class' => 'MetaModelFilterSettingConditionAnd',
	'image' => 'system/modules/metamodels/html/filter_and.png',
	'info_callback' => array('TableMetaModelFilterSetting', 'drawAndCondition'),
	'nestingAllowed' => true
);

$GLOBALS['METAMODELS']['filters']['conditionor'] = array
(
	'class' => 'MetaModelFilterSettingConditionOr',
	'image' => 'system/modules/metamodels/html/filter_or.png',
	'info_callback' => array('TableMetaModelFilterSetting', 'drawOrCondition'),
	'nestingAllowed' => true
);

// enable support for php 5.2
require_once(TL_ROOT . '/system/modules/metamodels/metamodel_functions.php');

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
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('TableMetaModelFilterSetting', 'createDataContainer');

$GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array('TableMetaModel', 'checkDependencies');

/**
 * Dependencies we need.
 */
$GLOBALS['METAMODELS']['dependencies'] = array(
	'metapalettes' => 'MetaPalettes',
	'multicolumnwizard' => 'MultiColumnWizard',
	'generalDriver' => 'DC_General'
);

?>