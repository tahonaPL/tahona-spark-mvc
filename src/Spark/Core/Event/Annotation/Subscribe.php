<?php
/**
 *
 *
 * Date: 09.10.16
 * Time: 20:18
 */

namespace Spark\Core\Event\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Subscribe {

    /**
     *  1. annotation name
     *  2. Class Name type
     *  3. field name
     *
     * @var string
     */
    public $name = null;
}