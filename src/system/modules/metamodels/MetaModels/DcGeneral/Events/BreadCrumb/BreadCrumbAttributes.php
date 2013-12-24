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

/**
 * Generate a breadcrumb for table tl_metamodel_attribute.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
 */
class BreadCrumbAttributes
	extends BreadCrumbMetaModels
{
	/**
	 * {@inheritDoc}
	 */
	public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
	{
		if (!isset($this->metamodelId))
		{
			$input = $environment->getInputProvider();

			$this->metamodelId = $input->getParameter('id');
		}

		$elements = parent::getBreadcrumbElements($environment, $elements);

		$elements[] = array(
			'url' => sprintf(
				'contao/main.php?do=metamodels&table=%s&id=%s',
				'tl_metamodel_attribute',
				$this->metamodelId
			),
			'text' => sprintf(
				$this->getBreadcrumbLabel($environment, 'tl_metamodel_attribute'),
				$this->getMetaModel()->getName()
			),
			'icon' => $this->getBaseUrl() . '/system/modules/metamodels/html/fields.png'
		);

		return $elements;
	}
}
