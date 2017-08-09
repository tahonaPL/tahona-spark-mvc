<?php

namespace spark\core\definition;

use spark\cache\service\CacheableServiceBeanProxy;
use spark\core\interceptor\HandlerInterceptor;
use spark\utils\Objects;
use spark\utils\StringUtils;

/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 08:43
 */
class BeanDefinition {

    const D_BEAN = "bean";
    const D_NAME = "name";

    private $name;
    private $bean;

    private $classNames;

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

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getBean() {
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


}