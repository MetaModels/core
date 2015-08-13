<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Events;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Helper class solely for use in BaseSubscriber::delayEvent().
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
     * @param Event                    $event      The event.
     *
     * @param string                   $eventName  The event name.
     *
     * @param EventDispatcherInterface $dispatcher The dispatcher.
     *
     * @return void
     */
    public function __invoke(Event $event, $eventName, $dispatcher)
    {
        $dispatcher->removeListener($eventName, $this);

        call_user_func($this->handler, $event, $eventName, $dispatcher);
    }
}
