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

namespace MetaModels\DcGeneral\Events\Table\RenderSetting;

use DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\Dca\RenderSetting;
use MetaModels\Factory as ModelFactory;
use MetaModels\Attribute\Factory as AttributeFactory;

/**
 * Event handler to populate the options array of the attribute type select widget.
 *
 * @package MetaModels\DcGeneral\Events\Table\Attribute
 */
class PropertyTemplate
{
	/**
	 * Provide options for template selection.
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 */
	public static function getOptions(GetPropertyOptionsEvent $event)
	{
		$model          = $event->getModel();
		$parentProvider = $event->getEnvironment()->getDataProvider('tl_metamodel_rendersettings');
		$renderSettings = $parentProvider->fetch($parentProvider->getEmptyConfig()->setId($model->getProperty('pid')));
		$objMetaModel   = ModelFactory::byId($renderSettings->getProperty('pid'));
		$objAttribute   = $objMetaModel->getAttributeById($model->getProperty('attr_id'));

		if (!$objAttribute)
		{
			return;
		}

		$event->setOptions(RenderSetting::getTemplatesForBase('mm_attr_' . $objAttribute->get('type')));
	}
}
