<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 21.03.17
 * Time: 14:05
 */

namespace Spark\Core\Error;


use Spark\Common\Type\Orderable;

abstract class ExceptionResolver implements Orderable {

    const CLASS_NAME = "Spark\\core\\error\\ExceptionResolver";

    abstract public function doResolveException($ex);

}