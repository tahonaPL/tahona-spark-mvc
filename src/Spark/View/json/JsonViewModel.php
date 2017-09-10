<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 05.07.14
 * Time: 22:24
 */

namespace Spark\View\json;


use Spark\Core\service\PropertyHelper;
use Spark\Http\HttpCode;
use Spark\Http\ResponseHelper;
use Spark\Http\Response;
use Spark\Utils\Objects;

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