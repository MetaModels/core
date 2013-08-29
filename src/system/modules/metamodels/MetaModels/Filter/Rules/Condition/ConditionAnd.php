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

namespace MetaModels\Filter\Rules\Condition;

use MetaModels\Filter\IFilter;
use MetaModels\Filter\FilterRule;

/**
 * This is the MetaModel filter interface.
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class ConditionAnd extends FilterRule
{
	/**
	 * The list of child filters that shall be evaluated.
	 *
	 * @var \MetaModels\Filter\IFilter[]
	 */
	protected $arrChildFilters = array();

	/**
	 * Create a new FilterRule instance.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Adds a child filter to this rule that will get evaluated when this rule is evaluated.
	 *
	 * @param \MetaModels\Filter\IFilter $objFilter the filter to add as child
	 *
	 * @return void
	 */
	public function addChild(IFilter $objFilter)
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
				// Empty array, no items allowed by this rule, break out.
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

