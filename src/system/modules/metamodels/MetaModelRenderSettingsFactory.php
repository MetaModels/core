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
 * This is the IMetaModelRenderSettings factory interface.
 *
 * To create a IMetaModelRenderSettings instance, call {@link MetaModelRenderSettingsFactory::byId()}
 *
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelRenderSettingsFactory implements IMetaModelRenderSettingsFactory
{
	/**
	 * @var IMetaModelRenderSettings[]
	 */
	protected static $arrInstances = array();

	/**
	 * {@inheritdoc}
	 */
	public static function collectAttributeSettings(IMetaModel $objMetaModel, $objSetting)
	{
		if ($objSetting->get('id'))
		{
			$objViewAttributes = Database::getInstance()->prepare('SELECT * FROM tl_metamodel_rendersetting WHERE pid=?')
											  ->execute($objSetting->get('id'));
			while ($objViewAttributes->next())
			{
				$objAttr = $objMetaModel->getAttributeById($objViewAttributes->attr_id);
				if ($objAttr)
				{
					$objAttrSetting = $objSetting->getSetting($objAttr->getColName());

					foreach ($objViewAttributes->row() as $strKey=>$varValue)
					{
						if($varValue)
						{
							$objAttrSetting->$strKey = deserialize($varValue);
						}
					}
					$objSetting->setSetting($objAttr->getColName(), $objAttrSetting);
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public static function byId(IMetaModel $objMetaModel, $intId = 0)
	{
		if (self::$arrInstances[$intId])
		{
			return self::$arrInstances[$intId];
		}

		$objDB = Database::getInstance();
		$objView = null;
		if ($intId)
		{
			$objView = Database::getInstance()->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE pid=? AND id=?')
											  ->execute($objMetaModel->get('id'), $intId);
			if (!$objView->numRows)
			{
				$intId = 0;
				$objView = NULL;
			}
		}

		if (!($intId || $objView))
		{
			// test if an default has been defined.
			$objView = Database::getInstance()->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE pid=? AND isdefault=1')
											  ->execute($objMetaModel->get('id'));
		}
		if (!$objView->numRows)
		{
			return NULL;
		}

		$objRenderSetting = new MetaModelRenderSettings($objView->row());
		self::$arrInstances[$intId] = $objSetting;

		// populate the view with the defaults.
		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$objSetting = $objAttribute->getDefaultRenderSettings();
			$objRenderSetting->setSetting($objAttribute->getColName(), $objSetting);
		}

		self::collectAttributeSettings($objMetaModel, $objRenderSetting);

		return $objRenderSetting;
	}
}

?>