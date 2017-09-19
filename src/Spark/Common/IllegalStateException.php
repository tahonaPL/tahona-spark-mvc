<?php
/**
 *
 *
 * Date: 30.05.15
 * Time: 11:07
 */

namespace Spark\Common;


class IllegalStateException extends \Exception {

    function __construct($message, $e = null) {
        parent::__construct($message, 0, $e);
    }
}