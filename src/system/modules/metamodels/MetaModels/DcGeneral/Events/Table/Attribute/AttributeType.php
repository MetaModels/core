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

namespace MetaModels\DcGeneral\Events\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\Factory as ModelFactory;
use MetaModels\Attribute\Factory as AttributeFactory;

/**
 * Event handler to populate the options array of the attribute type select widget.
 *
 * @package MetaModels\DcGeneral\Events\Table\Attribute
 */
class AttributeType
{
	/**
	 * Provide options for attribute type selection.
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 */
	public static function getOptions(GetPropertyOptionsEvent $event)
	{
		$translator   = $event->getEnvironment()->getTranslator();
		$objMetaModel = ModelFactory::byId($event->getModel()->getProperty('pid'));
		$attributes   = AttributeFactory::getAttributeTypes(
			$objMetaModel->isTranslated(),
			$objMetaModel->hasVariants()
		);

		$options = array();
		foreach ($attributes as $attributeType)
		{
			$options[$attributeType] = $translator->translate('typeOptions.' . $attributeType, 'tl_metamodel_attribute');
		}

		$event->setOptions($options);
	}
}
