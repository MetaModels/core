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

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use MetaModels\Factory;

/**
 * Handle event to draw a render setting.
 *
 * @package MetaModels\DcGeneral\Events\Table\RenderSettings
 */
class DrawSetting
{
	/**
	 * Draw the render setting.
	 *
	 * @param ModelToLabelEvent $event The event.
	 *
	 * @return void
	 */
	public static function modelToLabel(ModelToLabelEvent $event)
	{
		$model        = $event->getModel();
		$objSetting   = \Database::getInstance()
			->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE id=?')
			->execute($model->getProperty('pid'));
		$objMetaModel = Factory::byId($objSetting->pid);

		$objAttribute = $objMetaModel->getAttributeById($model->getProperty('attr_id'));

		if ($objAttribute)
		{
			$type  = $objAttribute->get('type');
			$image = $GLOBALS['METAMODELS']['attributes'][$type]['image'];
			if (!$image || !file_exists(TL_ROOT . '/' . $image))
			{
				$image = 'system/modules/metamodels/html/filter_default.png';
			}
			$label = $objAttribute->getName();
		}
		else
		{
			$type  = 'unknown ID: ' . $model->getProperty('attr_id');
			$image = 'system/modules/metamodels/html/filter_default.png';
			$label = 'unknown attribute';
		}

		/** @var GenerateHtmlEvent $imageEvent */
		$imageEvent = $event->getEnvironment()->getEventPropagator()->propagate(
			ContaoEvents::IMAGE_GET_HTML,
			new GenerateHtmlEvent($image)
		);

		$event->setLabel(
			sprintf(
			$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['row'],
			$imageEvent->getHtml(),
			$label ? $label : $type,
			$type
		));
	}
}
