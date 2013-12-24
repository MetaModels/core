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

namespace MetaModels\DcGeneral\Events\Table\MetaModels;

use DcGeneral\Contao\BackendBindings;
use DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;

class Subscriber
{
	/**
	 * Clear the button if the User is not admin.
	 *
	 * @param GetOperationButtonEvent $event
	 */
	public static function getOperationButton(GetOperationButtonEvent $event)
	{
		if (!\BackendUser::getInstance()->isAdmin)
		{
			$event->setHtml('');
		}
	}

	/**
	 * Clear the button if the User is not admin.
	 *
	 * @param GetGlobalButtonEvent $event
	 */
	public static function getGlobalButton(GetGlobalButtonEvent $event)
	{
		if (!\BackendUser::getInstance()->isAdmin)
		{
			$event->setHtml('');
		}
	}
	public static function modelToLabel(ModelToLabelEvent $event)
	{
		$model = $event->getModel();

		$translator = $event->getEnvironment()->getTranslator();

		if(!($model && \Database::getInstance()->tableExists($model->getProviderName(), null, true)))
			return;

		$strLabel = vsprintf($event->getLabel(), $event->getArgs());

		// add image
		$strImage = '';
		if ($model->getProperty('addImage'))
		{
			$arrSize = deserialize($model->getProperty('size'));
			$strImage = sprintf('<div class="image" style="padding-top:3px"><img src="%s" alt="%%1$s" /></div> ',
				BackendBindings::getImage($model->getProperty('singleSRC'), $arrSize[0], $arrSize[1], $arrSize[2]),
				htmlspecialchars($strLabel)
			);
		}

		// count items
		$objCount = \Database::getInstance()->prepare("SELECT count(*) AS itemCount FROM ".$model->getProperty('tableName'))
			->execute();
		$itemCount =  sprintf(
			' <span style="color:#b3b3b3; padding-left:3px">[%s]</span>',
			$translator->translatePluralized('itemFormat', $objCount->itemCount, 'tl_metamodel', array($objCount->itemCount))
		);

		$event->setArgs('<span class="name">' . $strLabel . $itemCount . '</span>' . $strImage);
	}


	public static function fixLangArray(DecodePropertyValueForWidgetEvent $event)
	{
		$langValues = (array)$event->getValue();
		$output     = array();
		foreach ($langValues as $langCode => $subValue)
		{
			if (is_array($subValue))
			{
				$output[] = array_merge($subValue, array('langcode' => $langCode));
			}
		}

		$event->setValue($output);
	}

	public static function unfixLangArray(EncodePropertyValueFromWidgetEvent $event)
	{
		$langValues  = (array)$event->getValue();
		$hasFallback = false;
		$output      = array();
		foreach ($langValues as $subValue)
		{
			$langCode = $subValue['langcode'];
			unset($subValue['langcode']);

			// we clear all subsequent fallbacks after we have found one.
			if($hasFallback)
			{
				$varSubValue['isfallback'] = '';
			}
			if($subValue['isfallback'])
			{
				$hasFallback = true;
			}
			$output[$langCode] = $subValue;
		}

		// if no fallback has been set, use the first language available.
		if((!$hasFallback) && count($output))
		{
			$output[$langValues[0]['langcode']]['isfallback'] = '1';
		}

		$event->setValue($output);
	}
}
