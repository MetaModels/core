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
 * This is the MetaModelFilterRule class for handling string value searches on attributes.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelFilterRuleSearchAttribute extends MetaModelFilterRule
{
	/**
	 * the attribute to search in.
	 * TODO: is it really a wise idea to keep a reference here? what about filter serialization for caching?
	 *
	 * @var IMetaModelAttribute
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
	 * @param string $strQueryString the query that shall be executed.
	 *
	 * @param array  $arrParams      the query parameters that shall be used.
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
		if (in_array('IMetaModelAttributeTranslated', class_implements($this->objAttribute)))
		{
			return $this->objAttribute->searchForInLanguages($this->strValue, $this->arrValidLanguages);
		} else {
			return $this->objAttribute->searchFor($this->strValue);
		}
	}
}

?>