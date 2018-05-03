<?php

namespace Spark\Core\Service;

use Spark\Utils\FilterUtils;
use Spark\View\ViewModel;

class PropertyHelper {

    use Properties;

    public function __construct($array = null) {
        if (!is_null($array)) {
            $this->addAll($array);
        }
    }
}
