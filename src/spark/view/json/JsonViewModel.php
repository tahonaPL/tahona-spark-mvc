<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 05.07.14
 * Time: 22:24
 */

namespace spark\view\json;


use spark\core\service\PropertyHelper;
use spark\http\HttpCode;
use spark\http\ResponseHelper;
use spark\http\Response;

class JsonViewModel extends PropertyHelper implements Response {
    public function __construct($array = null) {
        parent::__construct($array);
    }
} 