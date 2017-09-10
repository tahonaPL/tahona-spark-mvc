<?php
namespace Spark\Core\filler;

use Spark\Common\type\Orderable;

interface Filler extends Orderable {

    public function getValue($name, $type);

}