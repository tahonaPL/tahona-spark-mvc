<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 09.10.16
 * Time: 20:18
 */

namespace Spark\Core\annotation;

use Doctrine\Common\Annotations\Annotation\Target;


/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Bean {

    /** @var string */
    public $name = "";

}