<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 30.01.17
 * Time: 21:09
 */

namespace spark\core;

use spark\core\annotation\Bean;
use spark\core\annotation\Configuration;
use spark\core\annotation\PostConstruct;
use spark\core\routing\RoutingDefinitionConverter;

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