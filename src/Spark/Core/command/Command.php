<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 01.06.17
 * Time: 18:33
 */

namespace Spark\Core\command;


use Spark\Core\command\input\InputInterface;
use Spark\Core\command\output\OutputInterface;

interface Command {

    public function getName();

    public function execute(InputInterface $in, OutputInterface $out);
}