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
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}


/**
 * This is the MetaModelFilterRule class for handling checkbox fields.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelFilterRuleSimpleQuery extends MetaModelFilterRule
{
	/**
	 * the query string
	 *
	 * @var string
	 */
	protected $strQueryString = null;

	/**
	 * the query parameters.
	 *
	 * @var array
	 */
	protected $arrParams = null;

	/**
	 * the name of the id column in the query.
	 *
	 * @var array
	 */
	protected $strIdColumn = null;

	/**
	 * creates an instance of a simple query filter rule.
	 *
	 * @param string $strQueryString the query that shall be executed.
	 *
	 * @param array  $arrParams      the query parameters that shall be used.
	 */
	public function __construct($strQueryString, $arrParams=array(), $strIdColumn = 'id')
	{
		parent::__construct(null);
		$this->strQueryString = $strQueryString;
		$this->arrParams = $arrParams;
		$this->strIdColumn = $strIdColumn;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMatchingIds()
	{
		$objDB = Database::getInstance();
		$objMatches = $objDB->prepare($this->strQueryString)
		                    ->execute($this->arrParams);
		return ($objMatches->numRows == 0) ? array() : $objMatches->fetchEach($this->strIdColumn);
	}
}

?>