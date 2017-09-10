<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 14.07.14
 * Time: 00:17
 */

namespace Spark\Core\annotation;

use Doctrine\Common\Annotations\Annotation\Target;



/**
 * @Annotation
 * @Component
 * @Target({"PROPERTY"})
 */
final class Value {

    /** @var string */
    public $name = "";

} 