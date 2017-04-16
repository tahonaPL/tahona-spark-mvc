<?php

namespace spark\common;


class IllegalArgumentException extends \Exception {

    function __construct($message) {
        parent::__construct($message);
    }
}