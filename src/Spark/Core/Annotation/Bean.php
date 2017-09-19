<?php
/**
 *
 *
 * Date: 09.10.16
 * Time: 20:18
 */

namespace Spark\Core\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;


/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Bean {

    /** @var string */
    public $name = "";

}