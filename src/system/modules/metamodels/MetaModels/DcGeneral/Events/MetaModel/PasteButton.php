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
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;

/**
 * This class handles the paste into and after button activation and deactivation for all MetaModels being edited.
 *
 * @package MetaModels\DcGeneral\Events\MetaModel
 */
class PasteButton
	extends BaseView
{
	/**
	 * Handle the paste into and after buttons.
	 *
	 * @param GetPasteButtonEvent $event The event.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException When more than one model is contained within the clipboard.
	 */
	public static function handle(GetPasteButtonEvent $event)
	{
		$environment = $event->getEnvironment();
		$model       = $event->getModel();
		$clipboard   = $environment->getClipboard();
		$contained   = $event->getContainedModels();
		$disablePA   = true;
		$disablePI   = true;

		if (count($contained) !== 1)
		{
			throw new \RuntimeException('Paste multiple is not supported at the moment, sorry.');
		}

		// We assume we only have either varbase or non varbase items in the clipboard, mixed contents are not supported.
		$containedModel = $contained->get(0);

		if ($model && $model->getId() && !$event->getCircularReference())
		{
			// Insert a varbase after any other varbase, for sorting.
			if ($containedModel->getProperty('varbase') == 1
				&& (!$event->getCircularReference())
				&& $model->getProperty('varbase') == 1
			)
			{
				$disablePA = false;
			}
			// Move items in their vargroup and only there.
			elseif ($containedModel->getProperty('varbase') == 0
				&& $containedModel->getProperty('vargroup') == $model->getProperty('vargroup')
				&& $containedModel->getProperty('varbase') != 1
			)
			{
				$disablePA = false;
			}
		}
		elseif($model == null && $containedModel->getProperty('varbase') == 0)
		{
			$disablePA = true;
		}
		else
		{
			$disablePA = false;
			// If we are in create mode, disable the paste into.
			$disablePI = !($containedModel->getProperty('varbase') == 1 && $clipboard->getMode() != 'create');
		}

		$event
			->setPasteAfterDisabled($disablePA)
			->setPasteIntoDisabled($disablePI);
	}
}
