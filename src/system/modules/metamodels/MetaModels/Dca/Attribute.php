<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Dca;

use MetaModels\Factory as ModelFactory;

/**
 * This class is used from tl_metamodel for various callbacks.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Attribute extends Helper
{
	/**
	 * @var Helper
	 */
	protected static $objInstance = null;
	 * Get the static instance.
	 *
	 * @static
	 * @return Helper
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null) {
			self::$objInstance = new Attribute();
		}
		return self::$objInstance;
	}

	/**
	 * Protected constructor for singleton instance.
	 */
	protected function __construct()
	{
		parent::__construct();
	}

	/**
	/**
	 * @param \DcGeneral\DataContainerInterface $objDC The DataContainer.
	 */
	public function onDeleteCallback($objDC)
	{
		$objField = AttributeFactory::createFromArray($objDC->getCurrentModel()->getPropertiesAsArray());
		if($objField)
		{
			$objField->destroyAUX();
		}
	}

	/**
	 * @param \DcGeneral\DataContainerInterface $objDC The DataContainer.
	 *
	 * @return \MetaModels\IMetaModel
	 */
	protected function getMetaModelFromDC($objDC)
	{
		return ModelFactory::byId($objDC->getEnvironment()->getCurrentModel()->getProperty('pid'));
	}

	/**
	 * Add the type of input field
	 * @param array
	 * @return string
	 */
	public function renderField($arrRow)
	{

		$objMetaModel = ModelFactory::byId($arrRow['pid']);

		$strColName = $arrRow['colname'];
		$strType = $arrRow['type'];
		$strImages = '';

		$arrName = deserialize($arrRow['name']);
		if(is_array($arrName))
		{
			$strName = $arrName[$GLOBALS['TL_LANGUAGE']];
			if(!$strName)
			{
				$strName = $arrName[$objMetaModel->getFallbackLanguage()];
			}
		} else {
			$strName = $arrRow['name'];
		}

		$arrDescription = deserialize($arrRow['description']);
		if(is_array($arrDescription))
		{
			$strDescription = $arrDescription[$GLOBALS['TL_LANGUAGE']];
			if(!$strDescription)
			{
				$strDescription = $arrDescription[$objMetaModel->getFallbackLanguage()];
			}
		} else {
			$strDescription = $arrRow['name'];
		}

		$strTypeImage = '<img src="' . $GLOBALS['METAMODELS']['attributes'][$strType]['image'] . '" />';

		return
			sprintf(
				'<div class="field_heading cte_type"><strong>%s</strong> <em>[%s]</em></div>
				<div class="field_type block">
					<div style="padding-top:3px; float:right;">%s</div>
					%s<strong>%s</strong> - %s<br />
					'.($arrRow['insertBreak'] ? '<span style="padding-left:20px;" class="legend" title="'.$GLOBALS['TL_LANG']['tl_metamodel_attribute']['legendTitle'][0].'"> '.$arrRow['legendTitle'] .'</span>' : '').'
</div>',
				$strColName,
				$strType,
				$strImages,
				$strTypeImage,
				$strName,
				$strDescription
			);
	}
}
