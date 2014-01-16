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

use DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;

/**
 * Central event subscriber implementation.
 *
 * @package MetaModels\DcGeneral\Events
 */
class Subscriber
	implements EventSubscriberInterface
{
	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  * The method name to call (priority defaults to 0)
	 *  * An array composed of the method name to call and the priority
	 *  * An array of arrays composed of the method names to call and respective
	 *    priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 *  * array('eventName' => 'methodName')
	 *  * array('eventName' => array('methodName', $priority))
	 *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
	 *
	 * @return array The event names to listen to.
	 */
	public static function getSubscribedEvents()
	{
		return array
		(
			sprintf('%s[%s]', BuildDataDefinitionEvent::NAME, 'tl_metamodel')
				=> array('registerTableMetaModelsEvents', -200),
			sprintf('%s[%s]', BuildDataDefinitionEvent::NAME, 'tl_metamodel_attribute')
				=> array('registerTableMetaModelAttributeEvents', -200),
			sprintf('%s[%s]', BuildDataDefinitionEvent::NAME, 'tl_metamodel_dca')
				=> array('registerTableMetaModelDcaEvents', -200),
			sprintf('%s[%s]', BuildDataDefinitionEvent::NAME, 'tl_metamodel_filter')
				=> array('registerTableMetaModelFilterEvents', -200),
		);
	}

	/**
	 * Create a closure creating an instance of the passed class and calling the named method.
	 *
	 * @param string $class  The class name.
	 *
	 * @param string $method The method name.
	 *
	 * @return callable
	 */
	public static function createClosure($class, $method)
	{
		return function($event) use($class, $method) {
			$reflection = new \ReflectionClass($class);
			$instance   = $reflection->newInstance();
			call_user_func(array($instance, $method), $event);
		};
	}

	/**
	 * Create a callback that delays the event execution.
	 *
	 * This is done by registering the event within the returned callback and unregistering it when the callback has
	 * been executed.
	 *
	 * This only works for non top level events (it needs at least one sub level, like the data container name).
	 *
	 * @param callable $handler  The event handler to execute.
	 *
	 * @param int      $priority The priority.
	 *
	 * @return callable
	 */
	public static function delayEvent($handler, $priority = 0)
	{
		return function(Event $event) use($handler, $priority)
		{
			$name = $event->getName();

			$chunks = explode('[', $name);
			array_pop($chunks);

			$listener = function($event) use($handler)
			{
				/** @var Event $event */
				$event->getDispatcher()->removeListener($event->getName(), $handler);
				call_user_func($handler, $event);
			};

			$event->getDispatcher()->addListener(implode('[', $chunks), $listener, $priority);
		};
	}

	/**
	 * Register multiple event listeners.
	 *
	 * @param array                    $listeners  The listeners to register.
	 *
	 * @param EventDispatcherInterface $dispatcher The event dispatcher to which the events shall be registered.
	 *
	 * @param string[]                 $suffixes   The suffixes for the event names to use.
	 *
	 * @param int                      $priority   The priority.
	 *
	 * @return void
	 */
	public static function registerListeners($listeners, $dispatcher, $suffixes = array(), $priority = 200)
	{
		$eventSuffix = '';
		foreach ($suffixes as $suffix)
		{
			$eventSuffix .= sprintf('[%s]', $suffix);
		}

		foreach ($listeners as $event => $listener)
		{
			$dispatcher->addListener($event . $eventSuffix, $listener, $priority);
		}
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
}
