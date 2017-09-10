<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 21.06.15
 * Time: 19:38
 */

namespace Spark\Common\exception;


class UnsupportedOperationException extends AbstractException {

    protected function getAlternativeMessage() {
        return "Unsupported operation or method invocation.";
    }
}