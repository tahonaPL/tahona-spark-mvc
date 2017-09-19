<?php
/**
 *
 *
 * Date: 25.01.15
 * Time: 21:20
 */

namespace Spark\Common\Exception;


use Spark\Utils\Objects;

class NotImplementedException extends AbstractException {
    protected function getAlternativeMessage() {
        return "Method is not implemented.";
    }
}