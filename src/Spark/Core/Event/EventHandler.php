<?php
/**
 *
 * 
 * Date: 2018-04-22
 * Time: 18:07
 */

namespace Spark\Core\Event;

interface EventHandler {

    public function handle(Event $event): void;
}