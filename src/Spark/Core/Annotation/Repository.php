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
 * @Target({"CLASS","ANNOTATION"})
 */
final class Repository {

    /** @var string */
    public $name = "";

} 