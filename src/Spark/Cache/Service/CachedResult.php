<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 08.06.17
 * Time: 21:28
 */

namespace Spark\Cache\Service;


class CachedResult {
    /**
     * @var \DateTime
     */
    private $dateTime;
    private $results;

    /**
     * CachedResult constructor.
     */
    public function __construct(\DateTime $dateTime, $results) {
        $this->dateTime = $dateTime;
        $this->results = $results;
    }

    public function getResults() {
        return $this->results;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate() {
        return $this->dateTime;
    }
}