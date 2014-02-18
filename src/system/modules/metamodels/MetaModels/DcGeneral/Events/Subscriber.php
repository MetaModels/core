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

namespace MetaModels\DcGeneral\Events;

use ContaoCommunityAlliance\Contao\EventDispatcher\Event\CreateEventDispatcherEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use DcGeneral\Event\PostPersistModelEvent;
use DcGeneral\Event\PreDeleteModelEvent;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\DcGeneral\Events\Table\InputScreen\PropertyPTable;
use MetaModels\DcGeneral\Events\Table\InputScreens\BuildPalette;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;

/**
 * Central event subscriber implementation.
 *
 * @package MetaModels\DcGeneral\Events
 */
class Subscriber
	extends BaseSubscriber
{
	/**
	 * Register all listeners to handle creation of a data container.
	 *
	 * @param CreateEventDispatcherEvent $event The event.
	 *
	 * @return void
	 */
	public static function registerEvents(CreateEventDispatcherEvent $event)
	{
		$dispatcher = $event->getEventDispatcher();
		// Handlers for build data definition.
		self::registerBuildDataDefinitionFor(
			'tl_metamodel',
			$dispatcher,
			__CLASS__ . '::registerTableMetaModelsEvents'
		);
		self::registerBuildDataDefinitionFor(
			'tl_metamodel_attribute',
			$dispatcher,
			__CLASS__ . '::registerTableMetaModelAttributeEvents'
		);
		self::registerBuildDataDefinitionFor(
			'tl_metamodel_dca',
			$dispatcher,
			__CLASS__ . '::registerTableMetaModelDcaEvents'
		);
		self::registerBuildDataDefinitionFor(
			'tl_metamodel_dca_combine',
			$dispatcher,
			__CLASS__ . '::registerTableMetaModelDcaCombineEvents'
		);
		self::registerBuildDataDefinitionFor(
			'tl_metamodel_dcasetting',
			$dispatcher,
			__CLASS__ . '::registerTableMetaModelDcaSettingEvents'
		);
		self::registerBuildDataDefinitionFor(
			'tl_metamodel_filter',
			$dispatcher,
			__CLASS__ . '::registerTableMetaModelFilterEvents'
		);
		self::registerBuildDataDefinitionFor(
			'tl_metamodel_filtersetting',
			$dispatcher,
			__CLASS__ . '::registerTableMetaModelFilterSettingEvents'
		);
		self::registerBuildDataDefinitionFor(
			'tl_metamodel_rendersetting',
			$dispatcher,
			__CLASS__ . '::registerTableMetaModelRenderSettingEvents'
		);
		self::registerBuildDataDefinitionFor(
			'tl_metamodel_rendersettings',
			$dispatcher,
			__CLASS__ . '::registerTableMetaModelRenderSettingsEvents'
		);
	}

	/**
	 * Register the events for table tl_metamodel.
	 *
	 * @param BuildDataDefinitionEvent $event The event being processed.
	 *
	 * @return void
	 */
	public static function registerTableMetaModelsEvents(BuildDataDefinitionEvent $event)
	{
		static $registered;
		if ($registered)
		{
			return;
		}
		$registered = true;
		$dispatcher = $event->getDispatcher();

		self::registerListeners(
			array(
				GetOperationButtonEvent::NAME => 'MetaModels\DcGeneral\Events\Table\MetaModels\Subscriber::getOperationButton',
				GetGlobalButtonEvent::NAME    => 'MetaModels\DcGeneral\Events\Table\MetaModels\Subscriber::getGlobalButton',
				ModelToLabelEvent::NAME       => 'MetaModels\DcGeneral\Events\Table\MetaModels\Subscriber::modelToLabel',
				GetBreadcrumbEvent::NAME      => self::createClosure(
						'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbMetaModels',
						'getBreadcrumb'
					),
			),
			$dispatcher,
			array('tl_metamodel')
		);

		// Save and load callbacks.
		self::registerListeners(
			array(
				DecodePropertyValueForWidgetEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\MetaModels\Subscriber::fixLangArray',
				EncodePropertyValueFromWidgetEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\MetaModels\Subscriber::unfixLangArray',
			),
			$dispatcher,
			array('tl_metamodel', 'languages')
		);

		// Save callbacks.
		self::registerListeners(
			array(
				EncodePropertyValueFromWidgetEvent::NAME
				=> 'MetaModels\DcGeneral\Events\Table\MetaModels\Subscriber::ensureTableNamePrefix',
			),
			$dispatcher,
			array('tl_metamodel', 'tableName')
		);

		// Global table events.
		self::registerListeners(
			array(
				PostPersistModelEvent::NAME
				=> 'MetaModels\DcGeneral\Events\Table\MetaModels\UpdateMetaModel::handle',
				PreDeleteModelEvent::NAME
				=> 'MetaModels\DcGeneral\Events\Table\MetaModels\DeleteMetaModel::handle',
			),
			$dispatcher,
			array('tl_metamodel')
		);
	}

	/**
	 * Register the events for table tl_metamodel_attribute.
	 *
	 * @param BuildDataDefinitionEvent $event The event being processed.
	 *
	 * @return void
	 */
	public static function registerTableMetaModelAttributeEvents(BuildDataDefinitionEvent $event)
	{
		static $registered;
		if ($registered)
		{
			return;
		}
		$registered = true;
		$dispatcher = $event->getDispatcher();

		self::registerListeners(
			array(
				GetBreadcrumbEvent::NAME
					=> self::createClosure('MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbAttributes', 'getBreadcrumb'),
				ModelToLabelEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\Attribute\DrawAttribute::modelToLabel',
			),
			$dispatcher,
			array('tl_metamodel_attribute')
		);

		self::registerListeners(
			array(
				GetPropertyOptionsEvent::NAME => 'MetaModels\DcGeneral\Events\Table\Attribute\AttributeType::getOptions',
			),
			$dispatcher,
			array('tl_metamodel_attribute', 'type')
		);

		// Save and load callbacks.
		self::registerListeners(
			array(
				DecodePropertyValueForWidgetEvent::NAME
				=> 'MetaModels\DcGeneral\Events\Table\Attribute\PropertyNameAndDescription::decodeValue',
				EncodePropertyValueFromWidgetEvent::NAME
				=> 'MetaModels\DcGeneral\Events\Table\Attribute\PropertyNameAndDescription::encodeValue',
				BuildWidgetEvent::NAME
				=> 'MetaModels\DcGeneral\Events\Table\Attribute\PropertyNameAndDescription::buildWidget',
			),
			$dispatcher,
			array('tl_metamodel_attribute', 'name')
		);

		// Save and load callbacks.
		self::registerListeners(
			array(
				DecodePropertyValueForWidgetEvent::NAME
				=> 'MetaModels\DcGeneral\Events\Table\Attribute\PropertyNameAndDescription::decodeValue',
				EncodePropertyValueFromWidgetEvent::NAME
				=> 'MetaModels\DcGeneral\Events\Table\Attribute\PropertyNameAndDescription::encodeValue',
				BuildWidgetEvent::NAME
				=> 'MetaModels\DcGeneral\Events\Table\Attribute\PropertyNameAndDescription::buildWidget',
			),
			$dispatcher,
			array('tl_metamodel_attribute', 'description')
		);

		// Global table events.
		self::registerListeners(
			array(
				PostPersistModelEvent::NAME
				=> 'MetaModels\DcGeneral\Events\Table\MetaModels\UpdateAttribute::handle',
				PreDeleteModelEvent::NAME
				=> 'MetaModels\DcGeneral\Events\Table\MetaModels\DeleteAttribute::handle',

			),
			$dispatcher,
			array('tl_metamodel_attribute')
		);
	}

	/**
	 * Register the events for table tl_metamodel_dca.
	 *
	 * @param BuildDataDefinitionEvent $event The event being processed.
	 *
	 * @return void
	 */
	public static function registerTableMetaModelDcaEvents(BuildDataDefinitionEvent $event)
	{
		static $registered;
		if ($registered)
		{
			return;
		}
		$registered = true;
		$dispatcher = $event->getDispatcher();

		self::registerListeners(
			array(
				ModelToLabelEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\InputScreen\ModelToLabel::render',
				PostPersistModelEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\InputScreen\UpdateInputScreen::handle',
				GetBreadcrumbEvent::NAME
					=> self::createClosure(
						'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbInputScreens',
						'getBreadcrumb'
					),
			),
			$dispatcher,
			array('tl_metamodel_dca')
		);

		self::registerListeners(
			array(
				ManipulateWidgetEvent::NAME => 'MetaModels\DcGeneral\Events\Table\InputScreen\PropertyPanelLayout::getWizard',
			),
			$dispatcher,
			array('tl_metamodel_dca', 'panelLayout')
		);

		self::registerListeners(
			array(
				GetPropertyOptionsEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\InputScreen\PropertyBackendSection::getSections',
			),
			$dispatcher,
			array('tl_metamodel_dca', 'backendsection')
		);

		self::registerListeners(
			array(
				GetPropertyOptionsEvent::NAME => 'MetaModels\DcGeneral\Events\Table\InputScreen\PropertyMode::getValidModes',
			),
			$dispatcher,
			array('tl_metamodel_dca', 'mode')
		);

		self::registerListeners(
			array(
				GetPropertyOptionsEvent::NAME => 'MetaModels\DcGeneral\Events\Table\InputScreen\PropertyPTable::getTables',
			),
			$dispatcher,
			array('tl_metamodel_dca', 'ptable')
		);

		PropertyPTable::setVisibility($event);
	}

	/**
	 * Register the events for table tl_metamodel_dca_combine.
	 *
	 * @param BuildDataDefinitionEvent $event The event being processed.
	 *
	 * @return void
	 */
	public static function registerTableMetaModelDcaCombineEvents(BuildDataDefinitionEvent $event)
	{
		static $registered;
		if ($registered)
		{
			return;
		}
		$registered = true;
		$dispatcher = $event->getDispatcher();

		self::registerListeners(
			array(
				GetBreadcrumbEvent::NAME => self::createClosure(
					'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbDcaCombine',
					'getBreadcrumb'
				),
			),
			$dispatcher,
			array('tl_metamodel_dca_combine')
		);
	}

	/**
	 * Register the events for table tl_metamodel_dcasetting.
	 *
	 * @param BuildDataDefinitionEvent $event The event being processed.
	 *
	 * @return void
	 */
	public static function registerTableMetaModelDcaSettingEvents(BuildDataDefinitionEvent $event)
	{
		static $registered;
		if ($registered)
		{
			return;
		}
		$registered = true;
		$dispatcher = $event->getDispatcher();

		self::registerListeners(
			array(
				ModelToLabelEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\InputScreens\ModelToLabel::handleModelToLabel',
				GetBreadcrumbEvent::NAME
					=> self::createClosure(
						'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbInputScreen',
						'getBreadcrumb'
					),
			),
			$dispatcher,
			array('tl_metamodel_dcasetting')
		);

		self::registerListeners(
			array(
				GetOperationButtonEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\InputScreens\InputScreenButtons::getToggleButton',
			),
			$dispatcher,
			array('tl_metamodel_dcasetting', 'toggle')
		);

		self::registerListeners(
			array(
				GetOperationButtonEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\InputScreens\InputScreenButtons::getSubPaletteButton',
			),
			$dispatcher,
			array('tl_metamodel_dcasetting', 'subpalette')
		);

		// Save and load callbacks.
		self::registerListeners(
			array(
				DecodePropertyValueForWidgetEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\InputScreens\PropertyLegendTitle::decodeValue',
				EncodePropertyValueFromWidgetEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\InputScreens\PropertyLegendTitle::encodeValue',
				BuildWidgetEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\InputScreens\PropertyLegendTitle::buildWidget',
			),
			$dispatcher,
			array('tl_metamodel_dcasetting', 'legendtitle')
		);

		self::registerListeners(
			array(
				GetPropertyOptionsEvent::NAME => 'MetaModels\DcGeneral\Events\Table\InputScreens\PropertyAttribute::getOptions',
			),
			$dispatcher,
			array('tl_metamodel_dcasetting', 'attr_id')
		);

		self::registerListeners(
			array(
				ManipulateWidgetEvent::NAME => 'MetaModels\DcGeneral\Events\Table\InputScreens\PropertyTlClass::getWizard',
			),
			$dispatcher,
			array('tl_metamodel_dcasetting', 'tl_class')
		);

		self::registerListeners(
			array(
				GetPropertyOptionsEvent::NAME => 'MetaModels\DcGeneral\Events\Table\InputScreens\PropertyRte::getOptions',
			),
			$dispatcher,
			array('tl_metamodel_dcasetting', 'rte')
		);

		BuildPalette::build($event);
	}

	/**
	 * Register the events for table tl_metamodel_filter.
	 *
	 * @param BuildDataDefinitionEvent $event The event being processed.
	 *
	 * @return void
	 */
	public static function registerTableMetaModelFilterEvents(BuildDataDefinitionEvent $event)
	{
		static $registered;
		if ($registered)
		{
			return;
		}
		$registered = true;
		$dispatcher = $event->getDispatcher();

		self::registerListeners(
			array(
				GetBreadcrumbEvent::NAME
					=> self::createClosure(
						'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbFilter',
						'getBreadcrumb'
					),
			),
			$dispatcher,
			array('tl_metamodel_filter')
		);
	}

	/**
	 * Register the events for table tl_metamodel_filtersetting.
	 *
	 * @param BuildDataDefinitionEvent $event The event being processed.
	 *
	 * @return void
	 */
	public static function registerTableMetaModelFilterSettingEvents(BuildDataDefinitionEvent $event)
	{
		static $registered;
		if ($registered)
		{
			return;
		}
		$registered = true;
		$dispatcher = $event->getDispatcher();

		self::registerListeners(
			array(
				GetPasteButtonEvent::NAME => 'MetaModels\DcGeneral\Events\Table\FilterSetting\PasteButton::generate',
			),
			$dispatcher,
			array('tl_metamodel_filtersetting')
		);

		self::registerListeners(
			array(
				GetPropertyOptionsEvent::NAME => 'MetaModels\DcGeneral\Events\Table\FilterSetting\PropertyDefaultId::getOptions',
			),
			$dispatcher,
			array('tl_metamodel_filtersetting', 'defaultid')
		);

		self::registerListeners(
			array(
				GetPropertyOptionsEvent::NAME => 'MetaModels\DcGeneral\Events\Table\FilterSetting\PropertyType::getOptions',
			),
			$dispatcher,
			array('tl_metamodel_filtersetting', 'type')
		);

		self::registerListeners(
			array(
				GetPropertyOptionsEvent::NAME => 'MetaModels\DcGeneral\Events\Table\FilterSetting\PropertyTemplate::getOptions',
			),
			$dispatcher,
			array('tl_metamodel_filtersetting', 'template')
		);

		self::registerListeners(
			array(
				GetPropertyOptionsEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\FilterSetting\PropertyAttributeId::getOptions',
				DecodePropertyValueForWidgetEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\FilterSetting\PropertyAttributeId::decodeValue',
				EncodePropertyValueFromWidgetEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\FilterSetting\PropertyAttributeId::encodeValue'
			),
			$dispatcher,
			array('tl_metamodel_filtersetting', 'attr_id')
		);

		foreach ($GLOBALS['METAMODELS']['filters'] as $typeName => $information)
		{
			if (isset($information['info_callback']))
			{
				self::registerListeners(
					array(
						ModelToLabelEvent::NAME => $information['info_callback']
					),
					$dispatcher,
					array('tl_metamodel_filtersetting', $typeName)
				);
			}
		}

		self::registerListeners(
			array(
				ModelToLabelEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\FilterSetting\DrawSetting::modelToLabel',
				GetBreadcrumbEvent::NAME
					=> self::createClosure('MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbFilterSetting', 'getBreadcrumb')
			),
			$dispatcher,
			array('tl_metamodel_filtersetting')
		);
	}

	/**
	 * Register the events for table tl_metamodel_rendersetting.
	 *
	 * @param BuildDataDefinitionEvent $event The event being processed.
	 *
	 * @return void
	 */
	public static function registerTableMetaModelRenderSettingEvents(BuildDataDefinitionEvent $event)
	{
		static $registered;
		if ($registered)
		{
			return;
		}
		$registered = true;
		$dispatcher = $event->getDispatcher();

		self::registerListeners(
			array(
				GetBreadcrumbEvent::NAME
					=> self::createClosure(
						'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbRenderSetting',
						'getBreadcrumb'
					)
			),
			$dispatcher,
			array('tl_metamodel_rendersetting')
		);

		self::registerListeners(
			array(
				GetPropertyOptionsEvent::NAME => 'MetaModels\DcGeneral\Events\Table\RenderSetting\PropertyTemplate::getOptions',
			),
			$dispatcher,
			array('tl_metamodel_rendersetting', 'template')
		);
	}

	/**
	 * Register the events for table tl_metamodel_rendersettings.
	 *
	 * @param BuildDataDefinitionEvent $event The event being processed.
	 *
	 * @return void
	 */
	public static function registerTableMetaModelRenderSettingsEvents(BuildDataDefinitionEvent $event)
	{
		static $registered;
		if ($registered)
		{
			return;
		}
		$registered = true;
		$dispatcher = $event->getDispatcher();

		self::registerListeners(
			array(
				GetBreadcrumbEvent::NAME
					=> self::createClosure(
						'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbRenderSettings',
						'getBreadcrumb'
					),
				PostPersistModelEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\RenderSettings\UpdateRenderSettings::handle',
				ModelToLabelEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\RenderSettings\DrawSetting::modelToLabel'
			),
			$dispatcher,
			array('tl_metamodel_rendersettings')
		);

		self::registerListeners(
			array(
				DecodePropertyValueForWidgetEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\RenderSettings\PropertyJumpTo::decodeValue',
				EncodePropertyValueFromWidgetEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\RenderSettings\PropertyJumpTo::encodeValue',
				BuildWidgetEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\RenderSettings\PropertyJumpTo::buildWidget',
			),
			$dispatcher,
			array('tl_metamodel_rendersettings', 'jumpTo')
		);

		self::registerListeners(
			array(
				GetPropertyOptionsEvent::NAME
					=> 'MetaModels\DcGeneral\Events\Table\RenderSettings\PropertyTemplate::getOptions',
			),
			$dispatcher,
			array('tl_metamodel_rendersettings', 'template')
		);
	}
}
