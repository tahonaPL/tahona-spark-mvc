<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 01.06.17
 * Time: 18:38
 */

namespace spark\core\command\input;


use spark\core\utils\SystemUtils;
use spark\http\utils\RequestUtils;

class InputInterface {

    public function get($key) {
        return SystemUtils::getParam($key);
    }
}