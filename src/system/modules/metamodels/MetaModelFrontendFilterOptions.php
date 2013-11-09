<?php

class MetaModelFrontendFilterOptions
{
	/*
	 * Auto submit
	 */
	protected $blnAutoSubmit		 = true;
	
	/*
	 * Hide clear filter
	 */
	protected $blnHideClearFilter	 = false;
	
	/*
	 * Show the count values
	 */
	protected $blnShowCountValues	 = false;
	
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

?>
