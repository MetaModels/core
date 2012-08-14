<?php
/**
 * The Catalog extension allows the creation of multiple catalogs of custom items,
 * each with its own unique set of selectable field types, with field extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each catalog.
 *
 * PHP version 5
 * @package	   Catalog
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

class CatalogTest
{

	public function doRun()
	{
		$objMetaModel = MetaModelFactory::byId(1);
		var_dump($objMetaModel->findByFilter(array('id' => '1,2')));
	}
}

?>