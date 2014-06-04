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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\InputScreenCondition;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\Factory;

/**
 * Draw a input screen element.
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreenCondition
 */
class ModelToLabel
{
	/**
	 * Retrieve the MetaModel attached to the model condition setting.
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
	 * Retrieve the label text for a condition setting or the default one.
	 *
	 * @param EnvironmentInterface $environment The environment in use.
	 *
	 * @param  string              $type        The type of the element.
	 *
	 * @return mixed|string
	 */
	public static function getLabelText(EnvironmentInterface $environment, $type)
	{
		$label = $environment->getTranslator()->translate('typedesc.' . $type, 'tl_metamodel_dcasetting_condition');
		if ($label == 'typedesc.' . $type)
		{
			$label = $environment->getTranslator()->translate('typedesc._default_', 'tl_metamodel_dcasetting_condition');
			if ($label == 'typedesc._default_')
			{
				return $type;
			}
		}
		return $label;
	}

	/**
	 * Render the html for the input screen condition.
	 *
	 * @param ModelToLabelEvent $event The event.
	 *
	 * @return void
	 */
	public static function handleModelToLabel(ModelToLabelEvent $event)
	{
		$environment    = $event->getEnvironment();
		$translator     = $environment->getTranslator();
		$model          = $event->getModel();

		$metaModels     = self::getMetaModel($environment);
		$attribute      = $metaModels->getAttributeById($model->getProperty('attr_id'));

		$type           = $model->getProperty('type');
		$parameterValue = $model->getProperty('value');
		$name           = $translator->translate('conditionnames.' . $type, 'tl_metamodel_dcasetting_condition');

		$image = $GLOBALS['METAMODELS']['attributes'][$type]['image'];
		if (!$image || !file_exists(TL_ROOT . '/' . $image))
		{
			$image = 'system/modules/metamodels/assets/images/icons/filter_default.png';
		}

		/** @var GenerateHtmlEvent $imageEvent */
		$imageEvent = $event->getEnvironment()->getEventPropagator()->propagate(
			ContaoEvents::IMAGE_GET_HTML,
			new GenerateHtmlEvent($image)
		);

		$event
			->setLabel(self::getLabelText($environment, $type))
			->setArgs(array(
					$imageEvent->getHtml(),
					$name,
					$attribute->getName(),
					$parameterValue
				));
	}
}
