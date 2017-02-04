<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 04.02.17
 * Time: 12:26
 */

namespace spark\core\resource;


use spark\utils\Collections;

class ResourcePath {

    public $paths;

    /**
     * Path constructor.
     */
    public function __construct($paths = array()) {
        $this->paths  = Collections::asArray($paths);
    }

    /**
     * @return array
     */
    public function getPaths() {
        return $this->paths;
    }


}