<?php

namespace Spark\Utils\Reflection;


class PropertyAnnotation {


    const D_ANNOTATION = "annotation";
    /**
     * @var \ReflectionProperty
     */
    private $reflectionProperty;


    private $annotation;

    /**
     * PropertyAnnotation constructor.
     * @param \ReflectionProperty $reflectionProperty
     * @param $annotation
     */
    public function __construct(\ReflectionProperty $reflectionProperty, $annotation) {
        $this->reflectionProperty = $reflectionProperty;
        $this->annotation = $annotation;
    }

    /**
     * @return \ReflectionProperty
     */
    public function getReflectionProperty(): \ReflectionProperty {
        return $this->reflectionProperty;
    }

    /**
     * @return mixed
     */
    public function getAnnotation() {
        return $this->annotation;
    }

}