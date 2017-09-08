<?php
namespace spark\core\filler;

use spark\cache\service\CacheableServiceBeanProxy;
use spark\core\annotation\Inject;
use spark\core\provider\BeanProvider;
use spark\utils\Asserts;
use spark\utils\Collections;
use spark\utils\Objects;

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
}