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
use MetaModels\Render\Setting\Factory;

class BreadCrumbRenderSettings
	extends BreadCrumbMetaModels
{
	/**
	 * @var int
	 */
	protected $renderSettingsId;

	/**
	 * @return \MetaModels\Render\Setting\ICollection
	 */
	protected function getRenderSettings()
	{
		return Factory::byId($this->getMetaModel(), $this->renderSettingsId);
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
		if (!isset($this->metamodelId))
		{
			$input = $environment->getInputProvider();
			$this->metamodelId = $input->getParameter('pid');
		}

		$elements = parent::getBreadcrumbElements($environment, $elements);

		$elements[] = array(
			'url' => sprintf(
				'contao/main.php?do=metamodels&table=%s&pid=%s',
				'tl_metamodel_rendersettings',
				$this->metamodelId
			),
			'text' => sprintf($this->getBreadcrumbLabel($environment, 'tl_metamodel_rendersettings'), $this->getMetaModel()->getName()),
			'icon' => $this->getBaseUrl() . '/system/modules/metamodels/html/render_settings.png'
		);

		return $elements;
	}
}
