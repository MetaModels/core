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
 * Interface for "simple" MetaModel attributes.
 * Simple attributes are attributes that only consist of one column in the metamodel table and therefore do not need
 * to be handled as complex fields must be.
 * 
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelAttributeSimple extends IMetaModelAttribute
{

	/**
	 * returns the SQL primitive type declaration in MySQL notation. i.e. "text NULL".
	 */
	public function getSQLDataType();

	/**
	 * Creates the underlying database structure for this attribute.
	 */
	public function createColumn();

	/**
	 * Removes the underlying database structure for this attribute.
	 */
	public function deleteColumn();

	/**
	 * Renames the underlying database structure for this attribute.
	 * 
	 * @param string $strNewColumnName the new column name for the attribute.
	 * 
	 * @return void
	 */
	public function renameColumn($strNewColumnName);




}

?>