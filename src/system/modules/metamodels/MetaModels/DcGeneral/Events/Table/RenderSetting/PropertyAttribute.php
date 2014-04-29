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

namespace MetaModels\DcGeneral\Events\Table\RenderSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\Factory;

/**
 * Handle events for tl_metamodel_dcasetting.attr_id.
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreens
 */
class PropertyAttribute
{
	/**
	 * Retrieve the options for the attributes.
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 */
	public static function getOptions(GetPropertyOptionsEvent $event)
	{
		$db        = \Database::getInstance();
		$model     = $event->getModel();
		$settings  = $db
			->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE id=?')
			->execute($model->getProperty('pid'));
		$metaModel = Factory::byId($settings->pid);

		if (!$metaModel)
		{
			return;
		}

		$arrResult = array();

		// Fetch all attributes that exist in other settings.
		$alreadyTaken = $db
			->prepare('
			SELECT
				attr_id
			FROM
				' . $model->getProviderName() . '
			WHERE
				attr_id<>?
				AND pid=?')
			->execute(
				$model->getProperty('attr_id'),
				$model->getProperty('pid')
			)
			->fetchEach('attr_id');

		foreach ($metaModel->getAttributes() as $attribute)
		{
			if (in_array($attribute->get('id'), $alreadyTaken))
			{
				continue;
			}
			$arrResult[$attribute->get('id')] = sprintf(
				'%s [%s]',
				$attribute->getName(),
				$attribute->get('type')
			);
		}

		$event->setOptions($arrResult);
	}
}
