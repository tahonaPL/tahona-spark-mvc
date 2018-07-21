<?php
/**
 * Date: 21.07.18
 * Time: 11:42
 */

namespace Spark\Http\Session;


use Spark\Http\Session;

interface SessionProvider {
    public function getOrCreateSession(): Session;

    public function getSession(): Session;

    public function hasSession(): bool;
}