<?php
namespace Spark\Core\filler;

use Spark\Cache\Service\CacheableServiceBeanProxy;
use Spark\Core\annotation\Inject;
use Spark\Core\provider\BeanProvider;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;
use Spark\Utils\Objects;

class BeanFiller implements Filler {


    /**
     * @Inject
     * @var BeanProvider
     */
    private $beanProvider;


    public function getValue($name, $type) {
        if ($this->beanProvider->hasBean($name)) {
            $bean = $this->beanProvider->getBean($name);
            if (Objects::isNotNull($bean) && Collections::contains($type, Objects::getClassNames($bean))) {
                return $bean;
            }
        }

        $beans = $this->beanProvider->getByType($type);
        Asserts::checkState(Collections::size($beans) <= 1, "Ambiguous type: $type injection!");

        $v = Collections::first($beans)->getOrNull();
        if ($v instanceof CacheableServiceBeanProxy) {
            return $v->getBean();
        }
        return $v;
    }

    public function getOrder() {
        return 120;
    }
}