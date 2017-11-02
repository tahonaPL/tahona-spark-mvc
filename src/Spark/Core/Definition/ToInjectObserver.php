<?php
/**
 *
 *
 * Date: 31.01.17
 * Time: 10:31
 */

namespace Spark\Core\Definition;


class ToInjectObserver {

    const D_ID                  = "id";
    const BEAN                  = "beanDef";
    const D_BEAN_NAME_TO_INJECT = "beanNameToInject";

    private $id;

    private $beanDef;
    private $beanNameToInject;


    /**
     * InjectToObserver constructor.
     * @param $bean
     * @param $beanNameToInject
     */
    public function __construct(&$beanDef, $beanNameToInject) {
        $this->beanDef = $beanDef;
        $this->beanNameToInject = $beanNameToInject;

        $this->id = "$beanNameToInject-" . spl_object_hash($this);
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
    public function getBeanDef(): BeanDefinition {
        return $this->beanDef;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }


}