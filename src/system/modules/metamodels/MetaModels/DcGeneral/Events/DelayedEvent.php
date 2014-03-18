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

use Symfony\Component\EventDispatcher\Event;

/**
 * Helper class solely for use in BaseSubscriber::delayEvent().
 *
 * @package MetaModels\DcGeneral\Events
 *
 * @see \MetaModels\DcGeneral\Events\BaseSubscriber::delayEvent().
 */
class DelayedEvent
{
	/**
	 * The event handler to be invoked.
	 *
	 * @var callable
	 */
	protected $handler;

	/**
	 * Create a new instance.
	 *
	 * @param callable $handler The handler to be invoked delayed.
	 */
	public function __construct($handler)
	{
		$this->handler = $handler;
	}

	/**
	 * Invoke the handler.
	 *
	 * @param Event $event The event.
	 *
	 * @return void
	 */
	public function __invoke(Event $event)
	{
		/** @var Event $event */
		$event->getDispatcher()->removeListener($event->getName(), $this);

		call_user_func($this->handler, $event);
	}
}
