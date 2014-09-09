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

namespace MetaModels\DcGeneral\Events;

use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base class for central event subscriber implementation.
 *
 * @package MetaModels\DcGeneral\Events
 */
class BaseSubscriber
{
    /**
     * Register a closure to the event dispatcher which will only be executed for the given container name.
     *
     * The closure checks if the BuildDataDefinitionEvent is for the container with the passed name, if so, the callback
     * will get executed.
     *
     * @param string                   $name       The name of the data container for which the callback shall be executed.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher to which the listener shall be attached.
     *
     * @param callable                 $callback   The callback to call.
     *
     * @param int                      $priority   The priority, defaults to -200.
     *
     * @return void
     */
    public static function registerBuildDataDefinitionFor(
        $name,
        EventDispatcherInterface $dispatcher,
        $callback,
        $priority = -200
    )
    {
        $dispatcher->addListener(
            BuildDataDefinitionEvent::NAME,
            function(BuildDataDefinitionEvent $event, $eventName, $dispatcher) use($name, $callback) {
                if ($event->getContainer()->getName() == $name)
                {
                    call_user_func($callback, $event, $eventName, $dispatcher);
                }
            },
            $priority
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
        return function(Event $event, $eventName, $dispatcher) use($handler, $priority)
        {
            /** @var EventDispatcherInterface $dispatcher */
            $chunks = explode('[', $eventName);
            array_pop($chunks);

            $dispatcher->addListener(
                implode('[', $chunks),
                new DelayedEvent($handler),
                $priority
            );
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
}
