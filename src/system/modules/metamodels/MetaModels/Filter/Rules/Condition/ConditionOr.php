<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Filter\Rules\Condition;

use MetaModels\Filter\FilterRule;
use MetaModels\Filter\IFilter;

/**
 * This is the MetaModel filter interface.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class ConditionOr extends FilterRule
{
	/**
	 * The list of child filters that shall be evaluated.
	 *
	 * @var IFilter[]
	 */
	protected $arrChildFilters = array();

	/**
	 * Flag determining if filtering shall return the first non-empty match.
	 *
	 * This flag determines if the rule shall return the result of the first
	 * child that returns at least one element (a return value of NULL from a
	 * rule counts as "all ids" in this context and therefore is considered
	 * non empty per definition).
	 *
	 * @var boolean
	 */
	protected $stopAfterMatch = false;

	/**
	 * Create a new FilterRule instance.
	 *
	 * @param boolean $stopAfterMatch Flag determining if filtering shall return
	 *                                the first non-empty match.
	 */
	public function __construct($stopAfterMatch = false)
	{
		parent::__construct();
		$this->stopAfterMatch = $stopAfterMatch;
	}

	/**
	 * Adds a child filter to this rule that will get evaluated when this rule is evaluated.
	 *
	 * @param IFilter $objFilter The filter to add as child.
	 *
	 * @return void
	 */
	public function addChild(IFilter $objFilter)
	{
		$this->arrChildFilters[] = $objFilter;
	}

	/**
	 * Fetch the ids from all child filter rules.
	 *
	 * If no entries have been found, the result is an empty array.
	 * If no filtering was applied and therefore all ids shall be reported as
	 * valid, the return value of NULL is allowed.
	 *
	 * The OR filter rule has an embedded shortcut for the first rule that
	 * returns "null". When this happens, no further child rules will get
	 * evaluated, as the result set can not expand any further.
	 *
	 * Note: when "stopAfterMatch" has been set, the rule will stop processing
	 * also when the first rule returns a non empty result and return that
	 * result.
	 *
	 * @return int[]|null
	 */
	public function getMatchingIds()
	{
		$arrIds = array();
		foreach ($this->arrChildFilters as $objChildFilter)
		{
			$arrChildMatches = $objChildFilter->getMatchingIds();
			// NULL => all items - for OR conditions, this can never be more than all so we are already satisfied here.
			if ($arrChildMatches === null)
			{
				return null;
			}

			if ($arrChildMatches && $this->stopAfterMatch)
			{
				return $arrChildMatches;
			}

			if ($arrChildMatches)
			{
				$arrIds = array_merge($arrIds, $arrChildMatches);
			}
		}
		return $arrIds;
	}
}

