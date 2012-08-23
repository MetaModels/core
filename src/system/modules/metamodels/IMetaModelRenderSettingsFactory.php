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
 * This is the IMetaModelRenderSettingsFactory factory interface.
 *
 * To create a IMetaModelRenderSettingsFactory instance, call {@link MetaModelRenderSettingsFactory::byId()}
 *
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelRenderSettingsFactory
{
	/**
	 * Load all attribute render information from the database and push the contained information into the settings object.
	 * You should not call this method directly but rather use {@link IMetaModelRenderSettingsFactory::byId} instead.
	 *
	 * @param IMetaModel               $objMetaModel the metamodel information for which the setting shall be retrieved.
	 *
	 * @param IMetaModelRenderSettings $objSetting   the render setting instance to be populated.
	 *
	 */
	public static function collectAttributeSettings(IMetaModel $objMetaModel, $objSetting);

	/**
	 * Create a IMetaModelRenderSettings instance from the id.
	 *
	 * @param IMetaModel $objMetaModel the metamodel information for which the setting shall be retrieved.
	 *
	 * @param int        $intId        the id of the IMetaModelRenderSettings.
	 *
	 * @return IMetaModelRenderSettings the instance of the IMetaModelRenderSettings or null if not found.
	 */
	public static function byId(IMetaModel $objMetaModel, $intId);
}

?>