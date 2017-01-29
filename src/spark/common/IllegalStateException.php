<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.05.15
 * Time: 11:07
 */

namespace spark\common;


class IllegalStateException extends \Exception {

    function __construct($message, $e = null) {
        parent::__construct($message, 0, $e);
    }
}