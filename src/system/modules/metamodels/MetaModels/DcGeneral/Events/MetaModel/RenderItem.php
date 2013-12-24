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

namespace MetaModels\DcGeneral\Events\MetaModel;

use DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\IItem;
use MetaModels\Items;
use MetaModels\Render\Setting\Factory;
use MetaModels\Render\Setting\ICollection;
use MetaModels\Render\Template;

class RenderItem
{
	/**
	 * @param \MetaModels\IItem $nativeItem
	 * @param ICollection       $renderSetting
	 *
	 * @return ICollection
	 */
	protected static function removeInvariantAttributes(IItem $nativeItem, ICollection $renderSetting)
	{
		$model = $nativeItem->getMetaModel();

		if ($model->hasVariants() && !$nativeItem->isVariantBase())
		{
			// Create a clone to have a separate copy of the object as we are going to manipulate it here.
			$renderSetting = clone $renderSetting;

			// Loop over all attributes and remove those from rendering that are not desired.
			foreach (array_keys($model->getInVariantAttributes()) as $strAttrName)
			{
				$renderSetting->setSetting($strAttrName, NULL);
			}
		}

		return $renderSetting;
	}

	/**
	 * Render the current item using the specified render setting.
	 *
	 * @param ModelToLabelEvent $event
	 */
	public static function render(ModelToLabelEvent $event)
	{
		$environment = $event->getEnvironment();
		/** @var IMetaModelDataDefinition $definition */
		$definition = $environment->getDataDefinition();

		/** @var Model $model */
		$model = $event->getModel();

		if (!($model instanceof Model))
		{
			return;
		}

		$nativeItem = $model->getItem();
		$metaModel  = $nativeItem->getMetaModel();

		$renderSetting = Factory::byId(
			$metaModel,
			$definition->getMetaModelDefinition()->getActiveRenderSetting()
		);

		if (!$renderSetting)
		{
			return;
		}

		$template      = new Template($renderSetting->get('template'));
		$renderSetting = self::removeInvariantAttributes($nativeItem, $renderSetting);

		/** @noinspection PhpUndefinedFieldInspection */
		$template->settings  = $renderSetting;
		/** @noinspection PhpUndefinedFieldInspection */
		$template->items     = new Items(array($nativeItem));
		/** @noinspection PhpUndefinedFieldInspection */
		$template->view      = $renderSetting;
		/** @noinspection PhpUndefinedFieldInspection */
		$template->data      = array($nativeItem->parseValue('html5', $renderSetting));

		$event->setLabel($template->parse('html5', true));
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
	 */
	public static function register($dispatcher)
	{
		$dispatcher->addListener(ModelToLabelEvent::NAME, array(__CLASS__, 'render'));
	}
}
