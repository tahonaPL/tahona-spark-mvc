<?php
/**
 *
 * User: crownclown67
 * Date: 01.06.17
 * Time: 18:38
 */

namespace Spark\Core\Command\Input;


use Spark\Core\Utils\SystemUtils;
use Spark\Http\Utils\RequestUtils;

class InputInterface {

    public function get($key) {
        return SystemUtils::getParam($key);
    }
}