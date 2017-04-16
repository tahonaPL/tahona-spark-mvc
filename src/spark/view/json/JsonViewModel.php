<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 05.07.14
 * Time: 22:24
 */

namespace spark\view\json;


use spark\http\HttpCode;
use spark\http\ResponseHelper;
use spark\view\ViewModel;

class JsonViewModel extends ViewModel {
    public function __construct($array = null) {
        parent::__construct($array);
        ResponseHelper::setCode(HttpCode::$OK);
    }

    public function getParams() {
        $params = parent::getParams();
        unset($params["web"]);
        return $params;
    }
} 