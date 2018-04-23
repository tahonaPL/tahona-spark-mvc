<?php
/**
 *
 * 
 * Date: 01.06.17
 * Time: 18:39
 */

namespace Spark\Core\Command\Output;


class OutputInterface {

    public function write($message) {
        echo $message;
    }

    public function writeln($message) {
        echo $message, PHP_EOL;
    }

    public function writeObject($object) {
        var_dump($object);
    }
}