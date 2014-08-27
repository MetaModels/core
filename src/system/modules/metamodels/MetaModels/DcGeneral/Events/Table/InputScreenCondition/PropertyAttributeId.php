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

namespace MetaModels\DcGeneral\Events\Table\InputScreenCondition;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\Factory;

/**
 * Event handlers for tl_metamodel_dcasetting_condition.attr_id.
 *
 * @package MetaModels\DcGeneral\Events\Table\FilterSetting
 */
class PropertyAttributeId
{
	/**
	 * Retrieve the MetaModel attached to the model filter setting.
	 *
	 * @param EnvironmentInterface $interface The environment.
	 *
	 * @return \MetaModels\IMetaModel
	 */
	public static function getMetaModel(EnvironmentInterface $interface)
	{
		$metaModelId = \Database::getInstance()
			->prepare('SELECT id FROM tl_metamodel WHERE
				id=(SELECT pid FROM tl_metamodel_dca WHERE
				id=(SELECT pid FROM tl_metamodel_dcasetting WHERE id=?))')
			->execute(IdSerializer::fromSerialized($interface->getInputProvider()->getParameter('pid'))->getId());

		return Factory::byId($metaModelId->id);
	}

	/**
	 * Prepares a option list with alias => name connection for all attributes.
	 *
	 * This is used in the attr_id select box.
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 */
	public static function getOptions(GetPropertyOptionsEvent $event)
	{
		$result    = array();
		$metaModel = self::getMetaModel($event->getEnvironment());

		foreach ($metaModel->getAttributes() as $attribute)
		{
			$typeName              = $attribute->get('type');
			$strSelectVal          = $metaModel->getTableName() .'_' . $attribute->getColName();
			$result[$strSelectVal] = $attribute->getName() . ' [' . $typeName . ']';
		}

		$event->setOptions($result);
	}

	/**
	 * Translates an attribute id to a generated alias {@see getAttributeNames()}.
	 *
	 * @param DecodePropertyValueForWidgetEvent $event The event.
	 *
	 * @return void
	 */
	public static function decodeValue(DecodePropertyValueForWidgetEvent $event)
	{
		$metaModel = self::getMetaModel($event->getEnvironment());
		$value     = $event->getValue();

		if (!($metaModel && $value))
		{
			return;
		}

		$attribute = $metaModel->getAttributeById($value);
		if ($attribute)
		{
			$event->setValue($metaModel->getTableName() .'_' . $attribute->getColName());
		}
	}

	/**
	 * Translates an generated alias {@see getAttributeNames()} to the corresponding attribute id.
	 *
	 * @param EncodePropertyValueFromWidgetEvent $event The event.
	 *
	 * @return void
	 */
	public static function encodeValue(EncodePropertyValueFromWidgetEvent $event)
	{
		$metaModel = self::getMetaModel($event->getEnvironment());
		$value     = $event->getValue();

		if (!($metaModel && $value))
		{
			return;
		}

		$value = str_replace($metaModel->getTableName() . '_', '', $value);

		$attribute = $metaModel->getAttribute($value);

		if ($attribute)
		{
			$event->setValue($attribute->get('id'));
		}
	}
}
