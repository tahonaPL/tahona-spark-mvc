<?php

namespace Spark\Common;


class IllegalArgumentException extends \Exception {

    public function __construct($message, $e = null) {
        parent::__construct($message, 0, $e);
    }
}