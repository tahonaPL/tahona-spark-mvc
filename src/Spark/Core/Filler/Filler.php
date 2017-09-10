<?php
namespace Spark\Core\Filler;

use Spark\Common\Type\Orderable;

interface Filler extends Orderable {

    public function getValue($name, $type);

}