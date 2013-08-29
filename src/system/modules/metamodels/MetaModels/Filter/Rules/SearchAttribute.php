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

namespace MetaModels\Filter\Rules;

use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\FilterRule;

/**
 * This is the MetaModelFilterRule class for handling string value searches on attributes.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class SearchAttribute extends FilterRule
{
	/**
	 * The attribute to search in.
	 *
	 * @var \MetaModels\Attribute\IAttribute|\MetaModels\Attribute\ITranslated
	 */
	protected $objAttribute = null;

	/**
	 * the value to search for.
	 *
	 * @var string
	 */
	protected $strValue = null;

	/**
	 * the valid languages to match (only used when searching a translated attribute)
	 *
	 * @var array
	 */
	protected $arrValidLanguages = null;

	/**
	 * creates an instance of a simple query filter rule.
	 *
	 * @param \MetaModels\Attribute\IAttribute $objAttribute      The attribute to be searched
	 *
	 * @param array                            $strValue          The value to be searched for. Wildcards (* and ? allowed)
	 *
	 * @param array                            $arrValidLanguages The list of valid languages to be searched in.
	 */
	public function __construct($objAttribute, $strValue=array(), $arrValidLanguages = array())
	{
		parent::__construct(null);
		$this->objAttribute = $objAttribute;
		$this->strValue = $strValue;
		$this->arrValidLanguages = $arrValidLanguages;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMatchingIds()
	{
		if (in_array('MetaModels\Attribute\ITranslated', class_implements($this->objAttribute)))
		{
			return $this->objAttribute->searchForInLanguages($this->strValue, $this->arrValidLanguages);
		} else {
			return $this->objAttribute->searchFor($this->strValue);
		}
	}
}

