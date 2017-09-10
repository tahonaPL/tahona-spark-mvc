<?php

namespace Spark\Core;

use Spark\Config;

interface ConfigAware {
    public function setConfig(Config $config);
}
