<?php
/**
 *
 *
 * Date: 30.01.17
 * Time: 10:53
 */

namespace Spark\Core\Provider;

use Spark\Container;
use Spark\Core\Processor\Cycle\BeanPostProcess;

class BeanProvider implements BeanPostProcess {
    public const NAME = 'beanProvider';

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

    public function hasBean($string) {
        return $this->container->hasBean($string);
    }

    public function getByType($className) {
        return $this->container->getByType($className);
    }

    public function afterInit(): void {
        $this->container = null;
    }
}