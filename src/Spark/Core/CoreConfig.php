<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 21:09
 */

namespace Spark\Core;

use Spark\Core\Annotation\Bean;
use Spark\Core\Annotation\Configuration;
use Spark\Core\Annotation\PostConstruct;
use Spark\Core\Routing\RoutingDefinitionConverter;

/**
 * @Configuration()
 */
class CoreConfig {


    /**
     * @PostConstruct()
     */
    public function init () {

    }

    /**
     * @Bean
     * @return RoutingDefinitionConverter
     */
    public function routingDefinitionConverter() {
        return new RoutingDefinitionConverter();
    }

}