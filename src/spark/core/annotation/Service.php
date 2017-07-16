<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 14.07.14
 * Time: 00:17
 */

namespace spark\core\annotation;

use Doctrine\Common\Annotations\Annotation\Target;



/**
 * @Annotation
 * @Component
 * @Target({"CLASS","ANNOTATION"})
 */
final class Service {

    /** @var string */
    public $name = "";

} 