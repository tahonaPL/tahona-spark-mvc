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
use spark\utils\Objects;

class JsonViewModel implements Response {

    private $obj;

    public function __construct($obj = null) {
        $this->obj = $obj;
    }

    /**
     * @return null
     */
    public function getObj() {
        return $this->obj;
    }

} 