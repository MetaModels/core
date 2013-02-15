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

	protected function prepareFrontendFilterOptions($arrWidget, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit)
	{
		$arrOptions = array();
		if(!$arrWidget['options'])
		{
			return $arrOptions;
		}
		$objController = MetaModelController::getInstance();

		$strFilterAction = '';

		// action for empty selection
		foreach($arrFilterUrl as $strKeyOption=>$strOption)
		{
			if($strKeyOption != $arrWidget['eval']['urlparam'] && $arrFilterUrl[$strKeyOption])
			{
				$strFilterAction .= '/'.$strKeyOption.'/'. urlencode($arrFilterUrl[$strKeyOption]);
			}
		}

		if (!$arrJumpTo)
		{
			$arrJumpTo = $GLOBALS['objPage']->row();
		}

		if ($arrWidget['eval']['includeBlankOption'])
		{
			$arrOptions[] = array
			(
				'key'    => '',
				'value'  => $GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter'],
				'href'   => $objController->generateFrontendUrl($arrJumpTo, $strFilterAction),
				'active' => (!$arrWidget['getparam'] ? true : false),
				'class'  => 'doNotFilter'.(!$arrWidget['getparam'] ? ' active' : ''),
			);
		}

		foreach ($arrWidget['options'] as $strKeyOption=>$strOption)
		{
			$arrOptions[] = array
			(
				'key'    => $strKeyOption,
				'value'  => $strOption,
				'href'   => $objController->generateFrontendUrl($arrJumpTo, $strFilterAction . '/'.$arrWidget['eval']['urlparam'].'/'.$strKeyOption),
				'active' => (($arrFilter['raw']['value'] && $arrWidget['getparam']==$arrWidget['raw']['value']) ? true : false),
				'class'  => $strKeyOption.($arrWidget['eval']['getparam']==$arrWidget['eval']['value'] ? ' active' : '')
				);
		}
		return $arrOptions;
	}

	protected function prepareFrontendFilterWidget($arrWidget, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit)
	{
		$strClass = $GLOBALS['TL_FFL'][$arrWidget['inputType']];

		// no widget? no output! that's it.
		if (!$strClass)
		{
			return array();
		}
		$arrWidget['value'] = $arrFilterUrl[$arrWidget['eval']['urlparam']];

		$arrData = MetaModelController::getInstance()->prepareForWidget($arrWidget, $arrWidget['eval']['urlparam'], $arrWidget['value']);

		if ($blnAutoSubmit)
		{
			$arrData['onchange'] = 'submit();';
		}

		$objWidget = new $strClass($arrData);

		$strField = $objWidget->generate();

		return array
		(
			'class'      => 'mm_'.$arrWidget['inputType'].' '.$arrWidget['urlparam'],
			'label'      => $objWidget->generateLabel(),
			'formfield'  => $strField,
			'raw'        => $arrWidget,
			'urlparam'   => $arrWidget['eval']['urlparam'],
			'getparam'   => $arrFilterUrl[$arrWidget['eval']['urlparam']],
			'options'    => $this->prepareFrontendFilterOptions($arrWidget, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit),
			'autosubmit' => $blnAutoSubmit,
			'urlvalue'   => $arrWidget['urlvalue'] ? $arrWidget['urlvalue'] : $arrWidget['value']
		);
	}

	//////////////////////////////////////////////////////////////////////////////
	// IMetaModelFilterSetting
	//////////////////////////////////////////////////////////////////////////////

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

	/**
	 * {@inheritdoc}
	 */
	public function getParameterFilterNames()
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterFilterWidgets($arrIds, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit)
	{
		return array();
	}
}

