<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 21:09
 */

namespace Spark\Core;

use Spark\Core\annotation\Bean;
use Spark\Core\annotation\Configuration;
use Spark\Core\annotation\PostConstruct;
use Spark\Core\routing\RoutingDefinitionConverter;

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