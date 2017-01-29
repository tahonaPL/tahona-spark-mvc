<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 05.07.15
 * Time: 11:06
 */

namespace spark\security\exception;


use spark\common\exception\AbstractException;

class AccessDeniedException extends AbstractException{

    protected function getAlternativeMessage() {
        return "Access denied.";
    }
}