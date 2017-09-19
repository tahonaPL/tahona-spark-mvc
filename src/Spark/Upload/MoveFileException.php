<?php
/**
 *
 *
 * Date: 29.07.14
 * Time: 22:26
 */

namespace Spark\Upload;


class MoveFileException extends \Exception {
    function __construct($message) {
        parent::__construct($message);
    }
} 