<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 21.03.17
 * Time: 14:05
 */

namespace spark\core\error;


use spark\common\type\Orderable;

abstract class ExceptionResolver implements Orderable{

    const CLASS_NAME = "spark\\core\\error\\ExceptionResolver";

    abstract public function doResolveException($ex);

}