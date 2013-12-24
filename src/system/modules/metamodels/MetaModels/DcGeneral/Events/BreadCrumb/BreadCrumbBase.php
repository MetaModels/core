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

use DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use DcGeneral\EnvironmentInterface;
use DcGeneral\InputProviderInterface;

abstract class BreadCrumbBase
{
	/**
	 * Get for a table the human readable name or a fallback
	 *
	 * @param \DcGeneral\EnvironmentInterface $environment
	 *
	 * @param string                          $table Name of table
	 *
	 * @return string Human readable name
	 */
	protected function getBreadcrumbLabel(EnvironmentInterface $environment, $table)
	{
		$shortTable = str_replace('tl_', '', $table);

		$label = $environment->getTranslator()->translate($shortTable, 'BRD');

		if ($label == $shortTable)
		{
			$shortTable = str_replace('tl_metamodel_', '', $table);
			return strtoupper(substr($shortTable, 0, 1)) . substr($shortTable, 1, strlen($shortTable) - 1) . ' %s';
		}

		return specialchars($label);
	}

	/**
	 * @return string
	 */
	protected function getBaseUrl()
	{
		return \Environment::getInstance()->base;
	}

	/**
	 * @param string $table
	 *
	 * @param InputProviderInterface $input
	 *
	 * @return mixed
	 */
	protected function isActiveTable($table, InputProviderInterface $input)
	{
		return $input->getParameter('table') == $table;
	}

	abstract public function getBreadcrumbElements(EnvironmentInterface $environment, $elements);

	public function getBreadcrumb(GetBreadcrumbEvent $event)
	{
		$environment = $event->getEnvironment();

		$event->setElements($this->getBreadcrumbElements($environment, array()));

		$event->stopPropagation();
	}
}
