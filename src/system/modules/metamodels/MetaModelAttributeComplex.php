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
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Base implementation for "complex" MetaModel attributes.
 * Complex fields are fields that can not be fetched with a simple "SELECT colName FROM cat_table" and therefore need
 * to be handled differently.
 * 
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
abstract class MetaModelAttributeComplex extends MetaModelAttribute implements IMetaModelAttributeComplex
{

	/**
	 * {@inheritdoc}
	 */
//	abstract public function getDataFor($arrIds);

	/**
	 * {@inheritdoc}
	 */
//	abstract public function setDataFor($arrValues);

	/**
	 * {@inheritdoc}
	 */
	public function parseFilterUrl($arrUrlParams)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIdsFromFilter($arrFilter)
	{
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function sortIds($arrIds, $strDirection)
	{
		return $arrIds;
	}
}

?>