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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\Factory;

/**
 * Handle events for property tl_metamodel_dcasetting_condition.value.
 */
class PropertyValue
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
	 * Provide options for the values contained within a certain attribute.
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 */
	public static function getOptions(GetPropertyOptionsEvent $event)
	{
		$model     = $event->getModel();
		$metaModel = self::getMetaModel($event->getEnvironment());
		$attribute = $metaModel->getAttributeById($model->getProperty('attr_id'));

		if ($attribute)
		{
			$event->setOptions($attribute->getFilterOptions(null, false));
		}
	}
}
