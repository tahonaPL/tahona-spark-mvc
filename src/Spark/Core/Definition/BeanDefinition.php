<?php

namespace Spark\Core\Definition;

use Spark\Cache\Service\CacheableServiceBeanProxy;
use Spark\Core\Interceptor\HandlerInterceptor;
use Spark\Utils\Objects;
use Spark\Utils\StringUtils;

/**
 *
 *
 * Date: 30.01.17
 * Time: 08:43
 */
class BeanDefinition {

    const D_BEAN = "bean";
    const D_NAME = "name";

    private $name;
    private $bean;

    private $classNames;
    private $ready = false;

    /**
     * BeanDefinition constructor.
     * @param $name
     * @param $bean
     */
    public function __construct($name, &$bean, array $classNames) {
        $this->name = $name;
        $this->bean = $bean;

        $this->classNames = $classNames;
    }

    public function ready() {
        $this->ready = true;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function &getBean() {
        return $this->bean;
    }

    /**
     * @return array
     */
    public function getClassNames() {
        return $this->classNames;
    }

    public function hasType($type) {
        return in_array($type, $this->classNames);
    }

    public function isReady() {
        return $this->ready;
    }



}