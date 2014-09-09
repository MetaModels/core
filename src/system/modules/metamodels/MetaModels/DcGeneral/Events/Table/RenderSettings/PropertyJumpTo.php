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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\RenderSettings;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use MetaModels\Factory;

/**
 * Event handler to define the extra value for the jumpTo MultiColumnWizard.
 *
 * @package MetaModels\DcGeneral\Events\Table\RenderSettings
 */
class PropertyJumpTo
{

	/**
	 * Translates the values of the jumpTo entries into the real array.
	 *
	 * @param DecodePropertyValueForWidgetEvent $event The event.
	 *
	 * @return void
	 */
	public static function decodeValue(DecodePropertyValueForWidgetEvent $event)
	{
		$propInfo = $event
			->getEnvironment()
			->getDataDefinition()
			->getPropertiesDefinition()
			->getProperty($event->getProperty());
		$value    = deserialize($event->getValue(), true);

		if (!$value)
		{
			return;
		}

		$extra = $propInfo->getExtra();

		$newValues    = array();
		$arrLanguages = $extra['columnFields']['langcode']['options'];

		foreach ($arrLanguages as $key => $lang)
		{
			$newValue  = '';
			$intFilter = 0;
			if ($value)
			{
				foreach ($value as $arr)
				{
					if (!is_array($arr))
					{
						break;
					}

					// Set the new value and exit the loop.
					if (array_search($key, $arr) !== false)
					{
						$newValue  = '{{link_url::'.$arr['value'].'}}';
						$intFilter = $arr['filter'];
						break;
					}
				}
			}

			// Build the new array.
			$newValues[] = array(
				'langcode' => $key,
				'value'    => $newValue,
				'filter'   => $intFilter
			);
		}

		$event->setValue($newValues);
	}

	/**
	 * Translates the values of the jumpTo entries into the internal array.
	 *
	 * @param EncodePropertyValueFromWidgetEvent $event The event.
	 *
	 * @return void
	 */
	public static function encodeValue(EncodePropertyValueFromWidgetEvent $event)
	{
		$value = deserialize($event->getValue(), true);

		foreach ($value as $k => $v)
		{
			$value[$k]['value'] = str_replace(
				array('{{link_url::', '}}'),
				array('',''),
				$v['value']
			);
		}

		$event->setValue(serialize($value));
	}

	/**
	 * Provide options for template selection.
	 *
	 * @param BuildWidgetEvent $event The event.
	 *
	 * @return void
	 */
	public static function buildWidget(BuildWidgetEvent $event)
	{
		$model      = $event->getModel();
		$metaModel  = Factory::byId($model->getProperty('pid'));
		$translator = $event->getEnvironment()->getTranslator();

		$extra = $event->getProperty()->getExtra();

		if ($metaModel->isTranslated())
		{
			$arrLanguages = array();
			foreach ((array)$metaModel->getAvailableLanguages() as $strLangCode)
			{
				$arrLanguages[$strLangCode] = $translator->translate('LNG.'. $strLangCode, 'languages');
			}
			asort($arrLanguages);

			$extra['minCount'] = count($arrLanguages);
			$extra['maxCount'] = count($arrLanguages);

			$extra['columnFields']['langcode']['options'] = $arrLanguages;
		}
		else
		{
			$extra['minCount'] = 1;
			$extra['maxCount'] = 1;

			$extra['columnFields']['langcode']['options'] = array
				(
					'xx' => $GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['jumpTo_allLanguages']
				);
		}

		$extra['columnFields']['filter']['options'] = self::getFilterSettings($model);

		$event->getProperty()->setExtra($extra);
	}

	/**
	 * Retrieve the model filters for the MCW.
	 *
	 * @param ModelInterface $model The model containing the currently edited render setting.
	 *
	 * @return array
	 */
	protected static function getFilterSettings(ModelInterface $model)
	{
		$objFilters = \Database::getInstance()
			->prepare('SELECT id, name FROM tl_metamodel_filter WHERE pid = ?')
			->execute($model->getProperty('pid'));

		$result = array();
		while ($objFilters->next())
		{
			$result[$objFilters->id] = $objFilters->name;
		}

		return $result;
	}
}
