<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 25.01.15
 * Time: 21:20
 */

namespace spark\common\exception;


use spark\utils\Objects;

class NotImplementedException extends AbstractException {
    protected function getAlternativeMessage() {
        return "Method is not implemented.";
    }
}