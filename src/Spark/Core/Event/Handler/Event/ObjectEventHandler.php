<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 2018-04-22
 * Time: 18:08
 */

namespace Spark\Core\Event\Handler\Event;

use Spark\Core\Event\Event;
use Spark\Core\Event\EventHandler;
use Spark\Utils\Objects;

class ObjectEventHandler implements EventHandler {

    private $object;
    private $methodName;

    public function __construct($object, $methodName) {
        $this->object = $object;
        $this->methodName = $methodName;
    }

    public function handle(Event $event): void {
        Objects::invokeMethod($this->object, $this->methodName, [$event]);
    }
}