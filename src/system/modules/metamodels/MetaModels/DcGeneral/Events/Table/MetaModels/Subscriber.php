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

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use MetaModels\Helper\TableManipulation;

/**
 * Handles event operations on tl_metamodel.
 *
 * @package MetaModels\DcGeneral\Events\Table\MetaModels
 */
class Subscriber
{
	/**
	 * Clear the button if the User is not admin.
	 *
	 * @param GetOperationButtonEvent $event The event.
	 *
	 * @return void
	 */
	public static function getOperationButton(GetOperationButtonEvent $event)
	{
		if (!\BackendUser::getInstance()->isAdmin)
		{
			$event->setHtml('');
		}

		$command = $event->getCommand();
		if ($command->getName() == 'dca_combine')
		{
			$parameters       = $command->getParameters();
			$parameters['id'] = IdSerializer::fromModel($event->getModel())
				->setDataProviderName('tl_metamodel_dca_combine')
				->getSerialized();
		}
	}

	/**
	 * Clear the button if the User is not admin.
	 *
	 * @param GetGlobalButtonEvent $event The event.
	 *
	 * @return void
	 */
	public static function getGlobalButton(GetGlobalButtonEvent $event)
	{
		if (!\BackendUser::getInstance()->isAdmin)
		{
			$event->setHtml('');
		}
	}

	/**
	 * Render a model in the backend list.
	 *
	 * @param ModelToLabelEvent $event The event.
	 *
	 * @return void
	 */
	public static function modelToLabel(ModelToLabelEvent $event)
	{
		$model = $event->getModel();

		$translator = $event->getEnvironment()->getTranslator();

		if (!($model && \Database::getInstance()->tableExists($model->getProviderName(), null, true)))
		{
			return;
		}

		$strLabel = vsprintf($event->getLabel(), $event->getArgs());

		$strImage = '';
		if ($model->getProperty('addImage'))
		{
			$arrSize    = deserialize($model->getProperty('size'));
			$imageEvent = new ResizeImageEvent($model->getProperty('singleSRC'), $arrSize[0], $arrSize[1], $arrSize[2]);

			$event->getEnvironment()->getEventPropagator()->propagate(ContaoEvents::IMAGE_RESIZE, $event);

			$strImage = sprintf('<div class="image" style="padding-top:3px"><img src="%s" alt="%%1$s" /></div> ',
				$imageEvent->getImage(),
				htmlspecialchars($strLabel)
			);
		}

		$objCount = \Database::getInstance()
			->prepare('SELECT count(*) AS itemCount FROM ' . $model->getProperty('tableName'))
			->execute();

		$itemCount =  sprintf(
			'<span style="color:#b3b3b3; padding-left:3px">[%s]</span>',
			$translator->translatePluralized(
				'itemFormatCount',
				$objCount->itemCount,
				'tl_metamodel',
				array($objCount->itemCount)
			)
		);
		$tableName = '<span style="color:#b3b3b3; padding-left:3px">(' . $model->getProperty('tableName') . ')</span>';

		$event->setArgs('<span class="name">' . $strLabel . $tableName . $itemCount . '</span>' . $strImage);
	}

	/**
	 * Decode a language array.
	 *
	 * @param DecodePropertyValueForWidgetEvent $event The event.
	 *
	 * @return void
	 */
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

	/**
	 * Encode a language array.
	 *
	 * @param EncodePropertyValueFromWidgetEvent $event The event.
	 *
	 * @return void
	 */
	public static function unfixLangArray(EncodePropertyValueFromWidgetEvent $event)
	{
		$langValues  = (array)$event->getValue();
		$hasFallback = false;
		$output      = array();
		foreach ($langValues as $subValue)
		{
			$langCode = $subValue['langcode'];
			unset($subValue['langcode']);

			// We clear all subsequent fallbacks after we have found one.
			if ($hasFallback)
			{
				$varSubValue['isfallback'] = '';
			}

			if ($subValue['isfallback'])
			{
				$hasFallback = true;
			}

			$output[$langCode] = $subValue;
		}

		// If no fallback has been set, use the first language available.
		if ((!$hasFallback) && count($output))
		{
			$output[$langValues[0]['langcode']]['isfallback'] = '1';
		}

		$event->setValue($output);
	}

	/**
	 * Called by tl_metamodel.tableName onsave_callback.
	 *
	 * Prefixes the table name with mm_ if not provided by the user as such.
	 * Checks if the table name is legal to the DB.
	 *
	 * @param EncodePropertyValueFromWidgetEvent $event The event.
	 *
	 * @return void
	 */
	public static function ensureTableNamePrefix(EncodePropertyValueFromWidgetEvent $event)
	{
		// See #49.
		$tableName = strtolower($event->getValue());

		if (!strlen($tableName))
		{
			throw new \RuntimeException('Table name not given');
		}

		// Force mm_ prefix.
		if (substr($tableName, 0, 3) !== 'mm_')
		{
			$tableName = 'mm_' . $tableName;
		}

		TableManipulation::checkTableDoesNotExist($tableName);

		$event->setValue($tableName);
	}
}
