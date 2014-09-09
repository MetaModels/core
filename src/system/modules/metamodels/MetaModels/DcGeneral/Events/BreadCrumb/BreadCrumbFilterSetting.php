<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\BreadCrumb;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Generate a breadcrumb for table tl_metamodel_filtersetting.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
 */
class BreadCrumbFilterSetting
	extends BreadCrumbFilter
{
	/**
	 * {@inheritDoc}
	 */
	public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
	{
		if (!isset($this->filterId))
		{
			$this->filterId = $this->extractIdFrom($environment, 'pid');
		}

		if (!isset($this->metamodelId))
		{
			$parent = \Database::getInstance()
				->prepare('SELECT id, pid, name FROM tl_metamodel_filter WHERE id=?')
				->executeUncached($this->filterId);

			$this->metamodelId = $parent->pid;
		}

		$filterSetting = $this->getFilter();

		$elements = parent::getBreadcrumbElements($environment, $elements);

		$elements[] = array(
			'url' => sprintf(
				'contao/main.php?do=metamodels&table=%s&pid=%s',
				'tl_metamodel_filtersetting',
				$this->seralizeId('tl_metamodel_filter', $this->filterId)
			),
			'text' => sprintf($this->getBreadcrumbLabel($environment, 'tl_metamodel_filtersetting'), $filterSetting->name),
			'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/icons/filter_setting.png'
		);

		return $elements;
	}
}
