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
class MetaModelFilterRuleStaticIdList extends MetaModelFilterRule
{

	/**
	 * The static id list that shall be applied.
	 */
	protected $arrIds = array();

	/**
	 * create a new FilterRule instance.
	 * @param IMetaModelAttribute $objAttribute the attribute this rule applies to.
	 */
	public function __construct($arrIds)
	{
		parent::__construct(null);
		$this->arrIds = $arrIds;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMatchingIds()
	{
		return $this->arrIds;
	}
}

?>
