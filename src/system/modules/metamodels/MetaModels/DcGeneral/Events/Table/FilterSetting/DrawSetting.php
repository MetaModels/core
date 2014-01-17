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

namespace MetaModels\DcGeneral\Events\Table\FilterSetting;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use DcGeneral\Data\ModelInterface;
use DcGeneral\EnvironmentInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DrawSetting
{
	/**
	 * @param ModelInterface       $model
	 *
	 * @param EnvironmentInterface $environment
	 *
	 * @return string
	 */
	public static function getLabelComment(ModelInterface $model, EnvironmentInterface $environment)
	{
		if ($model->getProperty('comment'))
		{
			return sprintf(
				$environment->getTranslator()->translate('typedesc._comment_', 'tl_metamodel_filtersetting'),
				specialchars($model->getProperty('comment'))
			);
		}
		return '';
	}

	public static function getLabelImage(ModelInterface $model, EventDispatcherInterface $dispatcher)
	{
		$type  = $model->getProperty('type');
		$image = $GLOBALS['METAMODELS']['filters'][$type]['image'];

		if (!$image || !file_exists(TL_ROOT . '/' . $image))
		{
			$image = 'system/modules/metamodels/html/filter_default.png';
		}

		if (!$model->getProperty('enabled'))
		{
			$intPos = strrpos($image, '.');
			if ($intPos !== false)
			{
				$image = substr_replace($image, '_1', $intPos, 0);
			}
		}

		/** @var AddToUrlEvent $urlEvent */
		$urlEvent = $dispatcher->dispatch(
			ContaoEvents::BACKEND_ADD_TO_URL,
			new AddToUrlEvent('act=edit&amp;id='.$model->getId())
		);

		/** @var GenerateHtmlEvent $imageEvent */
		$imageEvent = $dispatcher->dispatch(
			ContaoEvents::IMAGE_GET_HTML,
			new GenerateHtmlEvent($image)
		);

		return sprintf(
			'<a href="%s">%s</a>',
			$urlEvent->getUrl(),
			$imageEvent->getHtml()
		);
	}

	public static function getLabelText(EnvironmentInterface $environment, ModelInterface $model)
	{
		$type  = $model->getProperty('type');
		$label = $environment->getTranslator()->translate('typenames.' . $type, 'tl_metamodel_filtersetting');
		if ($label == 'typenames.' . $type)
		{
			return $type;
		}
		return $label;
	}

	public static function getLabelPattern(EnvironmentInterface $environment, ModelInterface $model)
	{
		$type = $model->getProperty('type');

		if (($resultPattern = $environment->getTranslator()->translate('typedesc.' . $type, 'tl_metamodel_filtersetting')) == 'typedesc.' . $type)
		{
			$resultPattern = $environment->getTranslator()->translate('typedesc._default_', 'tl_metamodel_filtersetting');
		}

		return $resultPattern;
	}

	public static function modelToLabelWithAttributeAndUrlParam(ModelToLabelEvent $event)
	{
		$environment = $event->getEnvironment();
		$model       = $event->getModel();
		$metamodel   = \MetaModels\Filter\Setting\Factory::byId($model->getProperty('fid'))->getMetaModel();
		$attribute   = $metamodel->getAttributeById($model->getProperty('attr_id'));

		if ($attribute)
		{
			$attributeName = $attribute->getColName();
		} else {
			$attributeName = $model->getProperty('attr_id');
		}

		$event
			->setLabel(self::getLabelPattern($environment, $model))
			->setArgs(array(
				self::getLabelImage($model, $event->getDispatcher()),
				self::getLabelText($environment, $model),
				self::getLabelComment($model, $environment),
				$attributeName,
				($model->getProperty('urlparam') ? $model->getProperty('urlparam') : $attributeName)
			))
			->stopPropagation();
	}

	public static function modelToLabelDefault(ModelToLabelEvent $event)
	{
		$environment = $event->getEnvironment();
		$model       = $event->getModel();

		$event
			->setLabel(self::getLabelPattern($environment, $model))
			->setArgs(array(
				self::getLabelImage($model, $event->getDispatcher()),
				self::getLabelText($environment, $model),
				self::getLabelComment($model, $environment),
				$model->getProperty('type')
			));
	}

	public static function modelToLabel(ModelToLabelEvent $event)
	{
		$environment = $event->getEnvironment();
		$model = $event->getModel();
		$type  = $model->getProperty('type');

		// Delegate the event further to the type handlers.
		if (!$environment->getEventPropagator()->propagateExact(
			$event::NAME,
			$event,
			array(
				$environment->getDataDefinition()->getName(),
				$type
			)
		)->isPropagationStopped())
		{
			// Handle with default drawing if no one wants to handle.
			self::modelToLabelDefault($event);
		}
	}
}
