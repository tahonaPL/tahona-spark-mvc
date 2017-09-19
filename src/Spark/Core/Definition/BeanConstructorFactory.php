<?php


namespace Spark\Core\Definition;


class BeanConstructorFactory {

    private $beanName;
    private $class;
    private $methodParameters;

    /**
     * BeanConstructorFactory constructor.
     * @param $beanName
     * @param $class
     * @param $methodParameters  array($name->$type)
     */
    public function __construct($beanName, $class, $methodParameters) {
        $this->beanName = $beanName;
        $this->class = $class;
        $this->methodParameters = $methodParameters;
    }


}