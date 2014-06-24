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
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use MetaModels\Factory;

/**
 * This class handels the paste into or after handling for variants.
 *
 * @package MetaModels\DcGeneral\Events\MetaModel
 */
class DuplicateModel
	extends BaseView
{
	/**
	 * Handle the paste into and after event.
	 *
	 * @param \ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent $event The event.
	 *
	 * @return void
	 */
	public static function handle(PostDuplicateModelEvent $event)
	{
		$model       = $event->getModel();
		$sourceModel = $event->getSourceModel();

		// Set the vargroup to null for auto creating.
		$model->setProperty('vargroup', null);
	}
}
