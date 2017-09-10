<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 01.06.17
 * Time: 18:38
 */

namespace Spark\Core\command\input;


use Spark\Core\utils\SystemUtils;
use Spark\Http\utils\RequestUtils;

class InputInterface {

    public function get($key) {
        return SystemUtils::getParam($key);
    }
}