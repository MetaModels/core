<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
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

/**
 * This class handles the paste into or after handling for variants.
 *
 * @package MetaModels\DcGeneral\Events\MetaModel
 */
class DuplicateModel
	extends BaseView
{
	/**
	 * Handle the paste into and after event.
	 *
	 * @param PostDuplicateModelEvent $event The event.
	 *
	 * @return void
	 */
	public static function handle(PostDuplicateModelEvent $event)
	{
		$model = $event->getModel();

		// Set the vargroup to null for auto creating.
		$model->setProperty('vargroup', null);
	}
}
