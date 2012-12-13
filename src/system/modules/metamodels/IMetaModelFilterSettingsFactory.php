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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * This is the IMetaModelFilterSettings factory interface.
 *
 * To create a IMetaModelFilterSettings instance, call {@link MetaModelFilterSettings::byId()}
 *
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelFilterSettingsFactory
{
	/**
	 * Create a IMetaModelFilterSettings instance from the id.
	 *
	 * @param int $intId the id of the IMetaModelFilterSettings.
	 *
	 * @return IMetaModelFilterSettings the instance of the IMetaModelFilterSettings or null if not found.
	 */
	public static function byId($intId);

	/**
	 * Query for all known MetaModel database tables.
	 *
	 * @return string[] all MetaModel table names as string array.
	 */
	// public static function getAllFor();
}

?>