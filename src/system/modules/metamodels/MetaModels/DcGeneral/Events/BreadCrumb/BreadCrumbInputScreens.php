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

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use DcGeneral\EnvironmentInterface;

/**
 * Generate a breadcrumb for table tl_metamodel_dca.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
 */
class BreadCrumbInputScreens
	extends BreadCrumbMetaModels
{
	/**
	 * Id of the input screen.
	 *
	 * @var int
	 */
	protected $inputScreenId;

	/**
	 * Retrieve the input screen database information.
	 *
	 * @return object
	 */
	protected function getInputScreen()
	{
		return (object)\Database::getInstance()
			->prepare('SELECT id, pid, name FROM tl_metamodel_dca WHERE id=?')
			->executeUncached($this->inputScreenId)
			->row();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
	{
		$input = $environment->getInputProvider();
		if (!$this->isActiveTable('tl_metamodel_dca', $input))
		{
			$this->inputScreenId = $this->extractIdFrom($environment, 'pid');
		}
		else
		{
			$this->metamodelId = $this->extractIdFrom($environment, 'pid');
		}

		if (!isset($this->metamodelId))
		{
			$this->metamodelId = $this->getInputScreen()->pid;
		}

		$elements = parent::getBreadcrumbElements($environment, $elements);

		$urlEvent = new AddToUrlEvent(sprintf('do=metamodels&table=%s&id=%s',
			'tl_metamodel_dca',
			$this->seralizeId('tl_metamodel', $this->metamodelId)
		));
		$environment->getEventPropagator()->propagate(ContaoEvents::BACKEND_ADD_TO_URL, $urlEvent);

		$elements[] = array(
			'url'  => $urlEvent->getUrl(),
			'text' => sprintf($this->getBreadcrumbLabel($environment, 'tl_metamodel_dca'), $this->getMetaModel()->getName()),
			'icon' => $this->getBaseUrl() . '/system/modules/metamodels/html/dca.png'
		);

		return $elements;
	}
}
