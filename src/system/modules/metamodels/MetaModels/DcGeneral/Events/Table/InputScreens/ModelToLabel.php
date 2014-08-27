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
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use MetaModels\Factory;

/**
 * Render an attribute in an input screen.
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreens
 */
class ModelToLabel
{
	/**
	 * Draw the attribute.
	 *
	 * @param ModelToLabelEvent $event The event.
	 *
	 * @return void
	 */
	protected static function drawAttribute(ModelToLabelEvent $event)
	{
		// FIXME: in here all language strings and icons are related to filters?
		// FIXME: Add language files for the error msg.

		$model        = $event->getModel();
		$objSetting   = \Database::getInstance()
			->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')
			->execute($model->getProperty('pid'));
		$objMetaModel = Factory::byId($objSetting->pid);

		$objAttribute = $objMetaModel->getAttributeById($model->getProperty('attr_id'));

		if ($objAttribute)
		{
			$type  = $objAttribute->get('type');
			$image = $GLOBALS['METAMODELS']['attributes'][$type]['image'];
			if (!$image || !file_exists(TL_ROOT . '/' . $image))
			{
				$image = 'system/modules/metamodels/assets/images/icons/fields.png';
			}
			$name    = $objAttribute->getName();
			$colName = $objAttribute->getColName();
		}
		else
		{
			$type    = 'unknown ID: ' . $model->getProperty('attr_id');
			$image   = 'system/modules/metamodels/assets/images/icons/fields.png';
			$name    = 'unknown attribute';
			$colName = 'unknown column';
		}

		/** @var GenerateHtmlEvent $imageEvent */
		$imageEvent = $event->getEnvironment()->getEventPropagator()->propagate(
			ContaoEvents::IMAGE_GET_HTML,
			new GenerateHtmlEvent($image)
		);

		$event
			->setLabel('<div class="field_heading cte_type %s"><strong>%s</strong> <em>[%s]</em></div>
				<div class="field_type block">
					%s<strong>%s</strong><span class="mandatory">%s</span> <span class="tl_class">%s</span>
				</div>')
			->setArgs(array(
				$model->getProperty('published') ? 'published' : 'unpublished',
				$colName,
				$type,
				$imageEvent->getHtml(),
				$name,
				$model->getProperty('mandatory') ? ' ['.$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['mandatory'][0].']' : '',
				$model->getProperty('tl_class') ? sprintf('[%s]', $model->getProperty('tl_class')) : ''
			));
	}

	/**
	 * Draw a legend.
	 *
	 * @param ModelToLabelEvent $event The event.
	 *
	 * @return void
	 */
	protected static function drawLegend(ModelToLabelEvent $event)
	{
		$model = $event->getModel();

		$arrLegend = deserialize($model->getProperty('legendtitle'));
		if (is_array($arrLegend))
		{
			$strLegend = $arrLegend[$GLOBALS['TL_LANGUAGE']];

			if (!$strLegend)
			{
				// TODO: Get the fallback language here.
				$strLegend = 'legend';
			}
		} else {
			$strLegend = $model->getProperty('legendtitle') ? $model->getProperty('legendtitle') : 'legend';
		}

		$event
			->setLabel('<div class="field_heading cte_type %s"><strong>%s</strong></div>
				<div class="dca_palette">%s%s</div>')
			->setArgs(array(
				$model->getProperty('published') ? 'published' : 'unpublished',
				$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatypes']['legend'],
				$strLegend,
				$model->getProperty('legendhide') ? ':hide' : ''
			));
	}

	/**
	 * Render an attribute or legend.
	 *
	 * @param ModelToLabelEvent $event The event.
	 *
	 * @return void
	 */
	public static function handleModelToLabel(ModelToLabelEvent $event)
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
