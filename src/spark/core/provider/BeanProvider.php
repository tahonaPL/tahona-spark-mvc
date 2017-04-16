<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 10:53
 */

namespace spark\core\provider;


use spark\Container;

class BeanProvider {

    /**
     * @var Container
     */
    private $container;

    /**
     * BeanProvider constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function getBean($string) {
        return $this->container->get($string);
    }

    public function getByType($className) {
        return $this->container->getByType($className);
    }


}