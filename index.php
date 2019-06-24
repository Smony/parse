<?php

require __DIR__.'/vendor/autoload.php';
use Symfony\Component\Console\Application;

use Command\CbsNewsCommand;
use Command\ChinaPlusCommand;
use Command\ChinaDailyCommand;

use Model\ParseLog\ParseLog;
use Carbon\Carbon;

$application = new Application();

$application->add(new CbsNewsCommand());
$application->add(new ChinaPlusCommand());
$application->add(new ChinaDailyCommand());

$application->run();
