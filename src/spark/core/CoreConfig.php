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
use spark\core\routing\RoutingDefinitionConverter;

/**
 * @Configuration()
 */
class CoreConfig {

    /**
     * @Bean
     * @return RoutingDefinitionConverter
     */
    private function routingDefinitionConverter() {
        return new RoutingDefinitionConverter();
    }

}