<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * This is the MetaModelFilterRule class for handling "greater-than" comparision on attributes.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelFilterRuleFilterAttributeGreaterThan implements IMetaModelFilterRule
{
	/**
	 * The attribute to search in.
	 *
	 * @var IMetaModelAttribute
	 */
	protected $objAttribute = null;

	/**
	 * The value to compare with.
	 *
	 * @var mixed
	 */
	protected $varValue = null;

	/**
	 * Determinator if the comparision shall be done inclusive or exclusive.
	 *
	 * @var boolean
	 */
	protected $blnInclusive = false;

	/**
	 * Creates an instance of this class.
	 *
	 * @param IMetaModelAttribute $objAttribute The attribute that shall be searched.
	 *
	 * @param array               $varValue     The value to compare against.
	 *
	 * @param bool                $blnInclusive If true, the passed value will be included in the check
	 *                                          and therefore make the check an equal-or-greater test.
	 */
	public function __construct($objAttribute, $varValue, $blnInclusive = false)
	{
		$this->objAttribute = $objAttribute;
		$this->varValue     = $varValue;
		$this->blnInclusive = $blnInclusive;
	}

	/**
	 * Fetch the ids for all items that hold a value that is greater than the passed value.
	 *
	 * If no entries have been found, the result is an empty array.
	 *
	 * @return int[]|null
	 */
	public function getMatchingIds()
	{
		return $this->objAttribute->filterGreaterThan($this->varValue, $this->blnInclusive);
	}
}

