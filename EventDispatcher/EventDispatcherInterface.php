<?php

namespace KumbiaPHP\EventDispatcher;

use KumbiaPHP\EventDispatcher\Event;

/**
 * Description of EventDispatcherInterface
 *
 * @author manuel
 */
interface EventDispatcherInterface
{

    public function dispatch($eventName, Event $event);

    public function addListener($eventName, $listener);

    public function hasListener($eventName, $listener);

    public function removeListener($eventName, $listener);
}