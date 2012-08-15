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
 * Controller class for DC_General
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class GeneralViewMetaModel extends GeneralViewDefault
{

	/**
	 * Create a new variant based upon the current item loaded in the data container.
	 *
	 * @param DC_General $objDC the data container with the loaded item.
	 *
	 * @return void
	 */
	public function createvariant(DC_General $objDC)
	{
		return $this->edit($objDC);
	}
}

?>
