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

namespace MetaModels\Dca;

use MetaModels\Factory as ModelFactory;

/**
 * This class is used from tl_metamodel for various callbacks.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Attribute extends Helper
{
	/**
	 * @var Helper
	 */
	protected static $objInstance = null;
	 * Get the static instance.
	 *
	 * @static
	 * @return Helper
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null) {
			self::$objInstance = new Attribute();
		}
		return self::$objInstance;
	}

	/**
	 * Protected constructor for singleton instance.
	 */
	protected function __construct()
	{
		parent::__construct();
	}

	/**
	 * @param \DcGeneral\DataContainerInterface $objDC The DataContainer.
	 *
	 * @return \MetaModels\IMetaModel
	 */
	protected function getMetaModelFromDC($objDC)
	{
		return ModelFactory::byId($objDC->getEnvironment()->getCurrentModel()->getProperty('pid'));
	}
}
