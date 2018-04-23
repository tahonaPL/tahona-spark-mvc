<?php
/**
 *
 * 
 * Date: 01.06.17
 * Time: 18:33
 */

namespace Spark\Core\Command;


use Spark\Core\Command\Input\InputInterface;
use Spark\Core\Command\Output\OutputInterface;

interface Command {

    public function getName();

    public function execute(InputInterface $in, OutputInterface $out);
}