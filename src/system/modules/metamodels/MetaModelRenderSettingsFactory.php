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
			$objViewAttributes = Database::getInstance()
					  ->prepare('SELECT * FROM tl_metamodel_rendersetting WHERE pid=? AND enabled=1 ORDER BY sorting')
					  ->execute($objSetting->get('id'));
			while ($objViewAttributes->next())
			{
				$objAttr = $objMetaModel->getAttributeById($objViewAttributes->attr_id);
				if ($objAttr)
				{
					$objAttrSetting = $objSetting->getSetting($objAttr->getColName());
					if (!$objAttrSetting)
					{
						$objAttrSetting = $objAttr->getDefaultRenderSettings();
					}

					foreach ($objViewAttributes->row() as $strKey=>$varValue)
					{
						if($varValue)
						{
							$objAttrSetting->set($strKey, deserialize($varValue));
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

		$objView = Database::getInstance()->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE pid=? AND (id=? OR isdefault=1) ORDER BY isdefault ASC')
										  ->limit(1)
										  ->execute($objMetaModel->get('id'), $intId);
		if (!$objView->numRows)
		{
			$intId = 0;
			$objView = NULL;
		}

		$objRenderSetting = new MetaModelRenderSettings($objView ? $objView->row(): array());
		self::collectAttributeSettings($objMetaModel, $objRenderSetting);
		return $objRenderSetting;
	}
}

