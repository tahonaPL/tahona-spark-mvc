<?php
/**
 *
 *
 * Date: 21.06.15
 * Time: 19:38
 */

namespace Spark\Common\Exception;


use Spark\Utils\Objects;

abstract class  AbstractException extends \Exception {
    function __construct($message = null, $code=0, $e = null) {
        if (Objects::isNotNull($message)) {
            parent::__construct($message, $code, $e);
        } else {
            parent::__construct($this->getAlternativeMessage(), $code, $e);
        }
    }

    protected function getAlternativeMessage(){
        return 'Error occurred';
    }

}