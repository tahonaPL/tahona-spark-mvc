<?php

namespace spark\core\definition;

use spark\utils\Objects;

/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 08:43
 */
class BeanDefinition {

    const D_BEAN = "bean";

    private $name;
    private $bean;

    private $classNames;

    /**
     * BeanDefinition constructor.
     * @param $name
     * @param $bean
     */
    public function __construct($name, &$bean) {
        $this->name = $name;
        $this->bean = $bean;

        $this->classNames = Objects::getClassNames($bean);
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
     * @return void
     */
    public function getClassNames() {
        return $this->classNames;
    }

    public function hasType($type) {
        return in_array($type, $this->classNames);
    }


}