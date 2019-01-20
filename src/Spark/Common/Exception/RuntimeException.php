<?php
/**
 * Date: 15.08.18
 * Time: 08:45
 */

namespace Spark\Common\Exception;


class RuntimeException extends AbstractException {

    protected function getAlternativeMessage() {
        return 'Runtime exception occurred!';
    }
}