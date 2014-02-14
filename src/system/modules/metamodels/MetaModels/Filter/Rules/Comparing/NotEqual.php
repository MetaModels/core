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

namespace MetaModels\Filter\Rules\Comparing;

use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\IFilterRule;

/**
 * This is the MetaModelFilterRule class for handling "not-equal" comparison on attributes.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class NotEqual implements IFilterRule
{
	/**
	 * The attribute to search in.
	 *
	 * @var IAttribute
	 */
	protected $objAttribute = null;

	/**
	 * The value to compare with.
	 *
	 * @var mixed
	 */
	protected $varValue = null;

	/**
	 * Creates an instance of this class.
	 *
	 * @param IAttribute $objAttribute The query that shall be executed.
	 *
	 * @param array      $varValue     The value to compare against.
	 */
	public function __construct($objAttribute, $varValue)
	{
		$this->objAttribute = $objAttribute;
		$this->varValue     = $varValue;
	}

	/**
	 * Fetch the ids for all items that hold a value that is not equal to the passed value.
	 *
	 * If no entries have been found, the result is an empty array.
	 *
	 * @return int[]|null
	 */
	public function getMatchingIds()
	{
		return $this->objAttribute->filterNotEqual($this->varValue);
	}
}

