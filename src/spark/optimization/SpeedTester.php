<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 19.01.15
 * Time: 21:25
 */

namespace spark\optimization;


class SpeedTester {

    private $startTimeMilliseconds;

    public function start() {
        $this->startTimeMilliseconds = microtime(true) * 1000;
    }

    public function getTime() {
        return (microtime(true) * 1000) - $this->startTimeMilliseconds;
    }

    public function displayTime($message=null) {
        var_dump($this->getTime().$message);
    }
}