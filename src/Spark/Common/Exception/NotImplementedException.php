<?php
/**
 *
 *
 * Date: 25.01.15
 * Time: 21:20
 */

namespace Spark\Common\Exception;


class NotImplementedException extends AbstractException {
    protected function getAlternativeMessage() {
        return 'Method is not implemented.';
    }
}