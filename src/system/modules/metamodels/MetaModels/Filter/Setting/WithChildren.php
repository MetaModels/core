<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\Render\Setting\ICollection as IRenderSettings;

/**
 * Base implementation for settings that can contain children.
 *
 * @see
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
abstract class WithChildren
	extends Simple
	implements IWithChildren
{
	/**
	 * All child settings embedded in this setting.
	 *
	 * @var ISimple[]
	 */
	protected $arrChildren = array();

	/**
	 *
	 * {@inheritdoc}
	 *
	 */
	public function addChild(ISimple $objFilterSetting)
	{
		$this->arrChildren[] = $objFilterSetting;
	}

	/**
	 * {@inheritdoc}
	 */
	public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting)
	{
		$arrFilterUrl = array();
		foreach ($this->arrChildren as $objSetting)
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
		foreach ($this->arrChildren as $objSetting)
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
		foreach ($this->arrChildren as $objSetting)
		{
			$arrParams = array_merge($arrParams, $objSetting->getParameterDCA());
		}
		return $arrParams;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterFilterWidgets(
		$arrIds,
		$arrFilterUrl,
		$arrJumpTo,
		FrontendFilterOptions $objFrontendFilterOptions
	)
	{
		$arrParams = array();
		foreach ($this->arrChildren as $objSetting)
		{
			$arrParams = array_merge(
				$arrParams,
				$objSetting->getParameterFilterWidgets($arrIds, $arrFilterUrl, $arrJumpTo, $objFrontendFilterOptions)
			);
		}
		return $arrParams;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterFilterNames()
	{
		$arrParams = array();
		foreach ($this->arrChildren as $objSetting)
		{
			$arrParams = array_merge($arrParams, $objSetting->getParameterFilterNames());
		}
		return $arrParams;
	}

	/**
	 * Retrieve a list of all referenced attributes within the filter setting.
	 *
	 * @return array
	 */
	public function getReferencedAttributes()
	{
		$arrAttributes = array();
		foreach ($this->arrChildren as $objSetting)
		{
			$arrAttributes = array_merge($arrAttributes, $objSetting->getReferencedAttributes());
		}
		return $arrAttributes;
	}
}

