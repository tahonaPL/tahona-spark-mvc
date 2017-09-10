<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 11.03.17
 * Time: 14:08
 */

namespace Spark\Core\Routing;


use Spark\Routing;
use Spark\Routing\RoutingUtils;
use Spark\Utils\Asserts;
use Spark\Utils\Collections;

class RoutingDefinitionConverter {

    public function toDefinitions($routing = array()) {
        $result = [];
        foreach ($routing as $key => $value) {
            $definition = new RoutingDefinition();
            $definition->setPath($key);
            $definition->setControllerClassName($value[Routing::CONTROLLER_NAME]);
            $definition->setActionMethod($value[Routing::METHOD_NAME]);
            $definition->setRequestHeaders(Collections::getValueOrDefault($value, Routing::REQUEST_HEADERS_NAME, array()));
            $definition->setRequestMethods(Collections::getValueOrDefault($value, Routing::REQUEST_METHODS_NAME, array()));
//            $definition->setRoles(Collections::getValueOrDefault($value, Routing::ROLES, array()));

            if (RoutingUtils::hasExpression($key)) {
                $definition->setParams(RoutingUtils::getParametrizedUrlKeys($key));
            }
            $result[] = $definition;

        }
        return $result;
    }
}