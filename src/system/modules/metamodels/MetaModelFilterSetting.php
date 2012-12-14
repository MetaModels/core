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
 * Base class for filter setting implementation.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
abstract class MetaModelFilterSetting implements IMetaModelFilterSetting
{
	/**
	 * The parenting filter setting container this setting belongs to.
	 *
	 * @var IMetaModelFilterSettings
	 */
	protected $objFilterSettings = null;

	protected $arrData = array();

	public function __construct($objFilterSetting, $arrData)
	{
		$this->objFilterSetting = $objFilterSetting;
		$this->arrData = $arrData;
	}

	public function get($strKey)
	{
		return $this->arrData[$strKey];
	}

	protected function getFilterSettings()
	{
		return $this->objFilterSetting;
	}

	protected function getMetaModel()
	{
		return $this->objFilterSetting->getMetaModel();
	}

	public function generateFilterUrlFrom(IMetaModelItem $objItem, IMetaModelRenderSettings $objRenderSetting)
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameters()
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterDCA()
	{
		return array();
	}
}

