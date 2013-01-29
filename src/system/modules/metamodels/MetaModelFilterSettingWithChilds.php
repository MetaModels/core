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
 *
 * Base implementation for settings that can contain childs.
 *
 * @see
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
abstract class MetaModelFilterSettingWithChilds
extends MetaModelFilterSetting
implements IMetaModelFilterSettingWithChilds
{
	/**
	 * all child settings embedded in this setting.
	 * @var IMetaModelFilterSetting[]
	 */
	protected $arrChilds = array();

	///////////////////////////////////////////////////////////////////////////////
	// Interface IMetaModelFilterSettingWithChilds
	///////////////////////////////////////////////////////////////////////////////

	/**
	 *
	 * {@inheritdoc}
	 *
	 */
	public function addChild(IMetaModelFilterSetting $objFilterSetting)
	{
		$this->arrChilds[] = $objFilterSetting;
	}

	/**
	 * {@inheritdoc}
	 */
	public function generateFilterUrlFrom(IMetaModelItem $objItem, IMetaModelRenderSettings $objRenderSetting)
	{
		$arrFilterUrl = array();
		foreach ($this->arrChilds as $objSetting)
		{
			$arrFilterUrl = array_merge($arrFilterUrl, $objSetting->generateFilterUrlFrom($objItem, $objRenderSetting));
		}
		return $arrFilterUrl;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameters()
	{
		$arrParams = array();
		foreach ($this->arrChilds as $objSetting)
		{
			$arrParams = array_merge($arrParams, $objSetting->getParameters());
		}
		return $arrParams;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterDCA()
	{
		$arrParams = array();
		foreach ($this->arrChilds as $objSetting)
		{
			$arrParams = array_merge($arrParams, $objSetting->getParameterDCA());
		}
		return $arrParams;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterFilterWidgets($arrIds, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit)
	{
		$arrParams = array();
		foreach ($this->arrChilds as $objSetting)
		{
			$arrParams = array_merge($arrParams, $objSetting->getParameterFilterWidgets($arrIds, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit));
		}
		return $arrParams;
	}
}

