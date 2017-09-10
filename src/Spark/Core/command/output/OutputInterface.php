<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 01.06.17
 * Time: 18:39
 */

namespace Spark\Core\command\output;


class OutputInterface {

    public function write($message) {
        print($message);
    }

    public function writeln($message) {
        print($message . PHP_EOL);
    }

    public function writeObject($object) {
        var_dump($object);
    }
}