<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 21.06.15
 * Time: 19:38
 */

namespace spark\common\exception;


use spark\utils\Objects;

abstract class  AbstractException extends \Exception {
    function __construct($message = null, $code=0, $e = null) {
        if (Objects::isNotNull($message)) {
            parent::__construct($message, $code, $e);
        } else {
            parent::__construct($this->getAlternativeMessage(), $code, $e);
        }
    }

    abstract protected function getAlternativeMessage();

}