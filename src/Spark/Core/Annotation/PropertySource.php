<?php
/**
 *
 *
 * Date: 14.07.14
 * Time: 00:17
 */

namespace Spark\Core\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;



/**
 * @Annotation
 * @Component
 * @Target({"PROPERTY"})
 */
final class PropertySource {

    /** @var string */
    public $name = "";

} 