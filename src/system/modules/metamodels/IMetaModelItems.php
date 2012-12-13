<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Interface for a collection of MetaModel items.
 *
 * @package	   MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelItems extends Iterator, ArrayAccess
{
	/**
	 * return the current item
	 *
	 * @return IMetaModelItem
	 */
	public function getItem();

	/**
	 * return the amount of contained items.
	 *
	 * @return int the amount of contained items.
	 */
	public function getCount();

	/**
	 * reset to the first element in the collection.
	 *
	 * @return bool true if there are items contained, false otherwise.
	 */
	public function first();

	/**
	 * advance the internal cursor by one.
	 *
	 * @return IMetaModelItems|boolean the current instance or false when last item has had been reached.
	 */
//	public function next();

	/**
	 * Go to the previous row of the current result
	 *
	 * @return IMetaModelItems|boolean the current instance or false if no previous item is present.
	 */
	public function prev();

	/**
	 * Go to the last row of the current result
	 *
	 * @return IMetaModelItems|boolean the current instance or false if no item is present.
	 */
	public function last();

	/**
	 * Reset the current result
	 *
	 * @return IMetaModelItems the current instance.
	 */
	public function reset();

	/**
	 * Get the CSS classes for the current item.
	 *
	 * The class will be combined of:
	 * * first - if the item is the first in the collection
	 * * last  - if the item is the first in the collection
	 * * even  - if the item is on even position
	 * * odd   - if the item is on odd position
	 *
	 * @return string the CSS class
	 */
	public function getClass();

	/**
	 * Parses the current item in the desired output format using the format settings.
	 *
	 * @param string      $strOutputFormat optional, defaults to text. The output format to use.
	 *
	 * @param object|null $objSettings     optional, defaults to null. The additional settings.
	 *
	 * @return array the parsed information.
	 */
	public function parseValue($strOutputFormat = 'text', $objSettings = NULL);

	/**
	 * Parses all items in the desired output format using the format settings.
	 *
	 * @param string      $strOutputFormat optional, defaults to text. The output format to use.
	 *
	 * @param object|null $objSettings     optional, defaults to null. The additional settings.
	 *
	 * @return array the parsed information.
	 */
	public function parseAll($strOutputFormat = 'text', $objSettings = NULL);

}

?>