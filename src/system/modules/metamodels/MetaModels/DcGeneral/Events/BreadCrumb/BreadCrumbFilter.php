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

class BreadCrumbFilter
	extends BreadCrumbMetaModels
{
	/**
	 * @var int
	 */
	protected $filterId;

	/**
	 * @return object
	 */
	protected function getFilter()
	{
		return (object) \Database::getInstance()
			->prepare('SELECT id, pid, name FROM tl_metamodel_filter WHERE id=?')
			->executeUncached($this->filterId)
			->row();
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
		$input = $environment->getInputProvider();
		if (!$this->isActiveTable('tl_metamodel_filter', $input))
		{
			$this->filterId = $input->getParameter('id');
		}
		else
		{
			$this->metamodelId = $input->getParameter('id');
		}

		if (!isset($this->metamodelId))
		{
			$this->metamodelId = $this->getFilter()->pid;
		}

		$elements = parent::getBreadcrumbElements($environment, $elements);

		$elements[] = array(
			'url' => sprintf(
				'contao/main.php?do=metamodels&table=%s&id=%s',
				'tl_metamodel_filter',
				$this->metamodelId
			),
			'text' => sprintf($this->getBreadcrumbLabel($environment, 'tl_metamodel_filter'), $this->getMetaModel()->getName()),
			'icon' => $this->getBaseUrl() . '/system/modules/metamodels/html/filter.png'
		);

		return $elements;
	}
}
