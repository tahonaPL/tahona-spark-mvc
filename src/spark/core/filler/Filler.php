<?php
namespace spark\core\filler;

use spark\common\type\Orderable;

interface Filler extends Orderable {

    public function getValue($name, $type);

}