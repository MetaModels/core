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
if (!defined('TL_ROOT')) {
	die('You cannot access this file directly!');
}

/**
 * This is the MetaModel filter interface.
 *
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelFilterRuleAND extends MetaModelFilterRule
{

	/**
	 * The static id list that shall be applied.
	 */
	protected $arrChildFilters = array();

	/**
	 * create a new FilterRule instance.
	 * @param IMetaModelAttribute $objAttribute the attribute this rule applies to.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	public function addChild(IMetaModelFilter $objFilter)
	{
		$this->arrChildFilters[] = $objFilter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMatchingIds()
	{
		$arrIds = NULL;
		$blnEmpty = true;
		foreach ($this->arrChildFilters as $objChildFilter)
		{
			$arrChildMatches = $objChildFilter->getMatchingIds();
			// null => all items allowed by this rule.
			if (is_null($arrChildMatches))
			{
				continue;
			}

			if ($arrChildMatches)
			{
				$blnEmpty = false;
				if (is_null($arrIds))
				{
					$arrIds = $arrChildMatches;
				} else {
					$arrIds = array_intersect($arrIds, $arrChildMatches);
				}
			} else {
				// empty array, no items allowed by this rule, break out.
				$arrIds = array();
				break;
			}
		}
		if ($blnEmpty)
		{
			return array();
		}
		return $arrIds;
	}
}

?>
