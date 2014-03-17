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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\Filter\Setting\Factory as FilterFactory;

/**
 * Handle events for property tl_metamodel_filtersetting.defaultid.
 */
class PropertyDefaultId
{
	/**
	 * Provide options for default selection.
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 */
	public static function getOptions(GetPropertyOptionsEvent $event)
	{
		$model = $event->getModel();

		$event->getEnvironment()->getInputProvider();

		$filterSetting = FilterFactory::byId($model->getProperty('fid'));
		$metaModel     = $filterSetting->getMetaModel();

		if (!$metaModel)
		{
			return;
		}

		$attribute = $metaModel->getAttributeById($model->getProperty('attr_id'));
		if (!$attribute)
		{
			return;
		}

		$onlyUsed = $model->getProperty('onlyused') ? true : false;

		$count   = array();
		$options = $attribute->getFilterOptions(null, $onlyUsed, $count);

		// Remove empty values.
		foreach ($options as $mixKey => $mixValue)
		{
			// Remove html/php tags.
			$mixValue = trim(strip_tags($mixValue));

			if (($mixValue === '') || ($mixValue === null) || ($onlyUsed && ($count[$mixKey] === 0)))
			{
				unset($options[$mixKey]);
			}
		}

		$event->setOptions($options);
	}
}
