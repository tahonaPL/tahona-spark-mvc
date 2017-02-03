<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 31.01.17
 * Time: 10:31
 */

namespace spark\core\definition;


class ToInjectObserver {

    const BEAN = "bean";
    const D_BEAN_NAME_TO_INJECT = "beanNameToInject";

    private $id;

    private $bean;
    private $beanNameToInject;


    /**
     * InjectToObserver constructor.
     * @param $bean
     * @param $beanNameToInject
     */
    public function __construct(&$bean, $beanNameToInject) {
        $this->bean = $bean;
        $this->beanNameToInject = $beanNameToInject;

        $this->id = uniqid("toInject");
    }

    /**
     * @return mixed
     */
    public function getBeanNameToInject() {
        return $this->beanNameToInject;
    }

    /**
     * @return mixed
     */
    public function getBean() {
        return $this->bean;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }



}