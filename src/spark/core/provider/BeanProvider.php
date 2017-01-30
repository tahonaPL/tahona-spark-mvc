<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 10:53
 */

namespace spark\core\provider;


use spark\Services;

class BeanProvider {

    /**
     * @var Services
     */
    private $services;

    /**
     * BeanProvider constructor.
     * @param Services $services
     */
    public function __construct(Services $services) {
        $this->services = $services;
    }

    public function getBean($string) {
        return $this->services->get($string);
    }

    public function getByType($className) {
        return $this->services->getByType($className);
    }


}