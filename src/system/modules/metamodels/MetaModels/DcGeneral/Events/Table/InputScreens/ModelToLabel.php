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

namespace MetaModels\DcGeneral\Events\Table\InputScreens;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use MetaModels\Factory;

class ModelToLabel
{
	protected static function drawAttribute(ModelToLabelEvent $event)
	{
		$model = $event->getModel();

		$objSetting = \Database::getInstance()->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')->execute($model->getProperty('pid'));
		$objMetaModel = Factory::byId($objSetting->pid);

		if (!$objMetaModel)
		{
			return;
		}

		$objAttribute = $objMetaModel->getAttributeById($model->getProperty('attr_id'));

		if (!$objAttribute)
		{
			return;
		}

		$strImage = $GLOBALS['METAMODELS']['attributes'][$objAttribute->get('type')]['image'];

		if (!$strImage || !file_exists(TL_ROOT . '/' . $strImage))
		{
			$strImage = 'system/modules/metamodels/html/filter_default.png';
		}

		$imageEvent = new GenerateHtmlEvent($strImage);

		$event->getEnvironment()->getEventPropagator()->propagate(ContaoEvents::IMAGE_GET_HTML, $imageEvent);

		$event
			->setLabel('%s <strong>%s</strong> %s <em>[%s]</em> <span class="tl_class">%s</span>')
			->setArgs(array(
				$imageEvent->getHtml(),
				$objAttribute->getName() ? $objAttribute->getName() : $objAttribute->get('type'),
				$model->getProperty('mandatory') ? '*' : '',
				$objAttribute->get('type'),
				$model->getProperty('tl_class') ? sprintf('[%s]', $model->getProperty('tl_class')) : ''
			));
	}

	protected static function drawLegend(ModelToLabelEvent $event)
	{
		$model = $event->getModel();

		$arrLegend = deserialize($model->getProperty('legendtitle'));
		if(is_array($arrLegend))
		{
			$strLegend = $arrLegend[$GLOBALS['TL_LANGUAGE']];

			if(!$strLegend)
			{
				// TODO: Get the fallback language here
				$strLegend = 'legend';
			}
		} else {
			$strLegend = $model->getProperty('legendtitle') ? $model->getProperty('legendtitle') : 'legend';
		}

		$event
			->setLabel('<div class="dca_palette">%s%s</div>')
			->setArgs(array(
				$strLegend,
				$model->getProperty('legendhide') ? ':hide' : ''
			));
	}

	public static function modelToLabel(ModelToLabelEvent $event)
	{
		$model = $event->getModel();

		switch ($model->getProperty('dcatype'))
		{
			case 'attribute':
				self::drawAttribute($event);
				break;

			case 'legend':
				self::drawLegend($event);
				break;

			default:
				break;
		}
	}
}
