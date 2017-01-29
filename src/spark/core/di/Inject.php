<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 14.07.14
 * Time: 00:17
 */

namespace spark\core\di;

use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\ORM\Mapping;


/**
 * @Annotation
 * @Target({"PROPERTY","ANNOTATION"})
 */
final class Inject implements Mapping\Annotation {

    /** @var string */
    public $name = "";

} 