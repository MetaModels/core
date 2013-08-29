<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

class FrontendFilterOptions
{
	/*
	 * Auto submit
	 */
	protected $blnAutoSubmit = true;

	/*
	 * Hide clear filter
	 */
	protected $blnHideClearFilter = false;

	/*
	 * Show the count values
	 */
	protected $blnShowCountValues = false;

	public function isAutoSubmit()
	{
		return $this->blnAutoSubmit;
	}

	public function setAutoSubmit($blnAutoSubmit)
	{
		$this->blnAutoSubmit = $blnAutoSubmit;
	}

	public function isHideClearFilter()
	{
		return $this->blnHideClearFilter;
	}

	public function setHideClearFilter($blnHideClearFilter)
	{
		$this->blnHideClearFilter = $blnHideClearFilter;
	}

	public function isShowCountValues()
	{
		return $this->blnShowCountValues;
	}

	public function setShowCountValues($blnShowCountValues)
	{
		$this->blnShowCountValues = $blnShowCountValues;
	}
}
