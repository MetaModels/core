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
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use DcGeneral\Contao\BackendBindings;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;

class InputScreenButtons
	extends InputScreenBase
{
	public static function getToggleButton(GetOperationButtonEvent $event)
	{
		$environment = $event->getEnvironment();
		$input       = $environment->getInputProvider();
		$model       = $event->getModel();
		if ($input->getParameter('tid') === $model->getId())
		{
			$model->setProperty('published', $input->getParameter('state') == 1);

			$environment
				->getDataProvider($model->getProviderName())
				->save($model);

			BackendBindings::redirect(BackendBindings::getReferer());
		}

		if (!$event->getModel()->getProperty('published'))
		{
			$extra = $event->getCommand()->getExtra();
			$extra['icon'] = 'invisible.gif';
			$event->getCommand()->setExtra($extra);
		}
	}

	public static function getSubPaletteButton(GetOperationButtonEvent $event)
	{
		$input = $event->getEnvironment()->getInputProvider();
		$model = $event->getModel();

		if (($model->getProperty('dcatype') !== 'attribute')
			|| (!self::getMetaModelFromModel($model))
			|| $input->getParameter('subpaletteid'))
		{
			$event->getCommand()->setDisabled();
			return;
		}

		// Get attribute and check if we have a valid one.
		$attribute = self::getMetaModelFromModel($model)->getAttributeById($model->getProperty('attr_id'));

		if(!($attribute && $attribute->get('type') == 'checkbox'))
		{
			$event->getCommand()->setDisabled($model->getProperty('attr_id'));
			return;
		}

		$urlEvent = new AddToUrlEvent('&pid='. $model->getProperty('pid') . '&subpaletteid='.$model->getProperty('id'));
		$event->getEnvironment()->getEventPropagator()->propagate(ContaoEvents::BACKEND_ADD_TO_URL, $urlEvent);

		$event->setHref($urlEvent->getUrl());
		$event->getCommand()->setDisabled(false);
	}
}


