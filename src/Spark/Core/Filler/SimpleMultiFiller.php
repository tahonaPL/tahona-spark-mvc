<?php
/**
 * Date: 06.09.18
 * Time: 05:13
 */

namespace Spark\Core\Filler;


use Spark\Common\Type\Orderable;
use Spark\Core\Annotation\Inject;
use Spark\Core\Annotation\PostConstruct;
use Spark\Core\Provider\BeanProvider;
use Spark\Utils\Collections;
use Spark\Utils\Objects;

class SimpleMultiFiller implements MultiFiller{

    /**
     * @Inject
     * @var BeanProvider
     */
    private $beanProvider;

    private $filers = [];

    /**
     * @PostConstruct()
     */
    public function init(): void {
        $this->filers = $this->beanProvider->getByType(Filler::class);
        $this->beanProvider = null;
    }


    public function filter(array $parameters): array {
        $params = [];

        if (Collections::isNotEmpty($parameters)) {
            foreach ($parameters as $paramName => $type) {
                $params[$paramName] = $this->getFillerValue($paramName, $type);
            }
        }
        return $params;
    }

    private function getFillerValue($paramName, $type) {
        /** @var Filler $filler */
        foreach ($this->filers as $filler) {
            $value = $filler->getValue($paramName, $type);
            if (Objects::isNotNull($value)) {
                return $value;
            }
        }
        return null;
    }

    /**
     * @return int
     */
    public function getOrder() {
        return 10;
    }
}