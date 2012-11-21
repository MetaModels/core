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
 * This is the IMetaModelFilter factory interface.
 *
 * To create a IMetaModelFilter instance, call {@link MetaModelFilter::byId()}
 *
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelFilterSettingsFactory implements IMetaModelFilterSettingsFactory
{
	/**
	 * Keeps track of all filter settings instances to save DB lookup queries.
	 *
	 * @var IMetaModelFilterSettings[]
	 */
	protected static $arrInstances = array();

	/**
	 * Create a IMetaModelFilter instance from the id.
	 *
	 * @param int $intId the id of the IMetaModelFilter.
	 *
	 * @return IMetaModelFilter the instance of the IMetaModelFilter or null if not found.
	 */
	public static function byId($intId)
	{
		if (!self::$arrInstances[$intId])
		{
			$objDB = Database::getInstance();

			$arrSettings = $objDB->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')
								 ->execute($intId)
								 ->row();
			if (!empty($arrSettings))
			{
				$objSetting = new MetaModelFilterSettings($arrSettings);
				$objSetting->collectRules();
			} else {
				$objSetting = new MetaModelFilterSettings(array());
			}
			self::$arrInstances[$intId] = $objSetting;
		} else {
			$objSetting = self::$arrInstances[$intId];
		}
		return $objSetting;
	}

	/**
	 * Query for all known MetaModel database tables.
	 *
	 * @return string[] all MetaModel table names as string array.
	 */
	// public static function getAllFor();
}

?>