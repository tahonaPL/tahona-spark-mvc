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
 * @Target({"CLASS"})
 */
final class Profile {

    /** @var string */
    public $name = "";

} 