<?php
/**
 *
 *
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
     * @Bean
     * @return RoutingDefinitionConverter
     */
    public function routingDefinitionConverter(): RoutingDefinitionConverter {
        return new RoutingDefinitionConverter();
    }

}