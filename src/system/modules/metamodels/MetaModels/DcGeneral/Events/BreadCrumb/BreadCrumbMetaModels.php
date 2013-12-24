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

namespace MetaModels\DcGeneral\Events\BreadCrumb;

use DcGeneral\EnvironmentInterface;
use MetaModels\Factory as MetaModelFactory;
use MetaModels\IMetaModel;

class BreadCrumbMetaModels
	extends BreadCrumbBase
{
	/**
	 * @var int
	 */
	protected $metamodelId;

	/**
	 * @return IMetaModel
	 */
	protected function getMetaModel()
	{
		return MetaModelFactory::byId($this->metamodelId);
	}

	/**
	 * @param \DcGeneral\EnvironmentInterface $environment
	 *
	 * @param array array                     $elements
	 *
	 * @return array
	 */
	public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
	{
		$elements[] = array(
			'url' => 'contao/main.php?do=metamodels',
			'text' => $this->getBreadcrumbLabel($environment, 'metamodels'),
			'icon' => $this->getBaseUrl() . 'system/modules/metamodels/html/logo.png'
		);

		return $elements;
	}
}
