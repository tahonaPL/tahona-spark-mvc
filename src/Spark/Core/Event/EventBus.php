<?php
/**
 *
 * 
 * Date: 2018-04-22
 * Time: 16:40
 */

namespace Spark\Core\Event;

use Spark\Common\Collection\FluentIterables;
use Spark\Utils\Collections;
use Spark\Utils\Objects;

class EventBus {

    public const NAME = 'eventBus';
    private $events = [];

    /**
     * @param $nameOrClassName - like "john" or Something::class
     * @param EventHandler $handler
     * @internal param \Closure $function
     */
    public function register($nameOrClassName, EventHandler $handler): void {
        $this->events[$nameOrClassName][] = $handler;
    }

    public function post(Event $event): void {
        $className = Objects::getClassName($event);

        $arr = Collections::getValue($this->events, $className);

        if (Objects::isNotNull($arr)) {
            FluentIterables::of($arr)
                ->each(function ($listener) use ($event) {
                    /** @var EventHandler $listener */
                    $listener->handle($event);
                });
        }
    }
}

