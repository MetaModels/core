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
use MetaModels\Factory;

/**
 * Generate a breadcrumb for table tl_metamodel_dcasetting.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
 */
class BreadCrumbInputScreen
	extends BreadCrumbInputScreens
{
	/**
	 * Calculate the name of a sub palette attribute.
	 *
	 * @param int $pid The id of the input screen.
	 *
	 * @return \MetaModels\Attribute\IAttribute|string
	 */
	protected function getSubPaletteAttributeName($pid)
	{
		$parent = \Database::getInstance()
			->prepare('SELECT id, pid
				FROM tl_metamodel_attribute
				WHERE id=(SELECT attr_id FROM tl_metamodel_dcasetting WHERE id=?)')
			->executeUncached($pid);
		if ($parent->id)
		{
			return Factory::byId($parent->pid)->getAttributeById($parent->id);
		}
		return 'unknown';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
	{
		$input = $environment->getInputProvider();
		if (!isset($this->inputScreenId))
		{
			$this->inputScreenId = $this->extractIdFrom($environment, 'pid');
		}

		$inputScreen = $this->getInputScreen();
		if (!isset($this->metamodelId))
		{
			$this->metamodelId = $inputScreen->pid;
		}

		$elements = parent::getBreadcrumbElements($environment, $elements);

		$elements[] = array(
			'url' => sprintf(
				'contao/main.php?do=metamodels&table=%s&pid=%s',
				'tl_metamodel_dcasetting',
				$this->seralizeId('tl_metamodel_dca', $this->inputScreenId)
			),
			'text' => sprintf($this->getBreadcrumbLabel($environment, 'tl_metamodel_dcasetting'), $inputScreen->name),
			'icon' => $this->getBaseUrl() . '/system/modules/metamodels/html/dca_setting.png'
		);

		if ($input->hasParameter('subpaletteid'))
		{
			$id = $this->extractIdFrom($environment, 'subpaletteid');

			$elements[] = array(
				'url' => sprintf(
					'contao/main.php?do=metamodels&table=%s&pid=%s&subpaletteid=%s',
					'tl_metamodel_dcasetting',
					$this->seralizeId('tl_metamodel_dca', $this->inputScreenId),
					$this->seralizeId('tl_metamodel_dcasetting', $id)
				),
				'text' => sprintf(
					$this->getBreadcrumbLabel($environment, 'metamodel_dcasetting_subpalette'),
					$this->getSubPaletteAttributeName($id)->getName()
				),
				'icon' => $this->getBaseUrl() . '/system/modules/metamodels/html/dca_setting.png'
			);
		}

		return $elements;
	}
}
