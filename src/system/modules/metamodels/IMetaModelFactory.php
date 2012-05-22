<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Interfaces
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
 * This is the MetaModel factory interface.
 * 
 * To create a MetaModel instance, either call {@link MetaModelFactory::byId()} or {@link MetaModelFactory::byTableName()}
 * 
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelFactory
{
	/**
	 * Create a MetaModel instance from the id.
	 * 
	 * @param int $intId the id of the MetaModel.
	 * 
	 * @return IMetaModel the instance of the MetaModel or null if not found.
	 */
	public static function byId($intId);

	/**
	 * Create a MetaModel instance from the table name.
	 * 
	 * @param string $strTablename the name of the table
	 * 
	 * @return IMetaModel the instance of the MetaModel or null if not found.
	 */
	public static function byTableName($strTablename);

	/**
	 * Query for all known MetaModel database tables.
	 * 
	 * @return string[] all MetaModel table names as string array.
	 */
	public static function getAllTables();
}

?>