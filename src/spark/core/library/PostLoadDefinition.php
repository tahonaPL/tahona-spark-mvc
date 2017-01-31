<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 31.01.17
 * Time: 09:40
 */

namespace spark\core\library;


use spark\utils\Objects;

class PostLoadDefinition {
    /**
     * @var string
     */
    private $configClass;
    /**
     * @var \Closure
     */
    private $condition;

    /**
     * PostLoadProcess constructor.
     * @param string $configClass
     * @param \Closure $param
     */
    public function __construct($configClass, \Closure $condition) {
        $this->configClass = $configClass;
        $this->condition = $condition;
    }

    public function canLoad() {
        $conditionClosure = $this->condition;
        return Objects::isNull($this->condition) || $conditionClosure();
    }

    public function getClass() {
        return $this->configClass;
    }
}