<?php
/**
 * Created by PhpStorm.
 * User: crownclown67
 * Date: 01.06.17
 * Time: 18:33
 */

namespace spark\core\command;


use spark\core\command\input\InputInterface;
use spark\core\command\output\OutputInterface;

interface Command {

    public function getName();

    public function execute(InputInterface $in, OutputInterface $out);
}