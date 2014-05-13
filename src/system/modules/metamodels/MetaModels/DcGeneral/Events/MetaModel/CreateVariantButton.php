<?php
/**
 * PHP version 5
 * @package    generalDriver
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostCreateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreCreateModelEvent;

class CreateVariantButton
	extends BaseView
{
	/**
	 * Check if we have to add the "Create variant" button.
	 *
	 * @param GetOperationButtonEvent $event
	 */
	public static function createButton(GetOperationButtonEvent $event)
	{
		$model     = $event->getModel();
		$metamodel = $model->getItem()->getMetaModel();

		if (!$metamodel->hasVariants() || $model ->getProperty('varbase') === '0')
		{
			$event->setHtml('');
		}
	}

	/**
	 * Handle the "create variant" event.
	 *
	 * @param ActionEvent $event
	 *
	 * @throws \RuntimeException
	 */
	public static function handleCreateVariantAction(ActionEvent $event)
	{
		$environment   = $event->getEnvironment();
		$view          = $environment->getView();
		$dataProvider  = $environment->getDataProvider();
		$inputProvider = $environment->getInputProvider();
		$modelId       = $inputProvider->hasParameter('id')
			? IdSerializer::fromSerialized($inputProvider->getParameter('id'))
			: null;

		/** @var \MetaModels\DcGeneral\Data\Driver $dataProvider */
		$model = $dataProvider
			->createVariant(
				$dataProvider
					->getEmptyConfig()
					->setId($modelId->getId())
			);

		if ($model == null)
		{
			throw new \RuntimeException(sprintf(
				'Could not find model with id %s for creating a variant.',
				$modelId
			));
		}

		$preFunction = function($environment, $model, $originalModel)
		{
			/** @var EnvironmentInterface $environment */
			$copyEvent = new PreCreateModelEvent($environment, $model);
			$environment->getEventPropagator()->propagate(
				$copyEvent::NAME,
				$copyEvent,
				array(
					$environment->getDataDefinition()->getName(),
				)
			);
		};

		$postFunction = function($environment, $model, $originalModel)
		{
			/** @var EnvironmentInterface $environment */
			$copyEvent = new PostCreateModelEvent($environment, $model);
			$environment->getEventPropagator()->propagate(
				$copyEvent::NAME,
				$copyEvent,
				array(
					$environment->getDataDefinition()->getName(),
				)
			);
		};

		$event->setResponse($view->createEditMask($model, null, $preFunction, $postFunction));
	}
}