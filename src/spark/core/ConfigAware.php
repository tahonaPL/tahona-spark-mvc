<?php

namespace spark\core;

use spark\Config;

interface ConfigAware {
    public function setConfig(Config $config);
}
