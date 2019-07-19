<?php

require __DIR__.'/vendor/autoload.php';
use Symfony\Component\Console\Application;

use Command\CbsNewsCommand;
use Command\ChinaPlusCommand;
use Command\ChinaDailyCommand;
use Command\CbsWorldCommand;
use Command\ChinaPlusPoliticsCommand;

use Command\RTNewsCommand;
use Command\RTNews2Command;
use Command\RTNews3Command;
use Command\EuroNewsCommand;

use Command\CountCommand;

use Command\IndependentCommand;
use Command\Independent2Command;

use Model\ParseLog\ParseLog;
use Carbon\Carbon;

$application = new Application();

$application->add(new CbsNewsCommand());
$application->add(new ChinaPlusCommand());
$application->add(new ChinaDailyCommand());
$application->add(new CbsWorldCommand());
$application->add(new ChinaPlusPoliticsCommand());

$application->add(new RTNewsCommand());
$application->add(new RTNews2Command());
$application->add(new RTNews3Command());
$application->add(new EuroNewsCommand());

$application->add(new CountCommand());

$application->add(new IndependentCommand());
$application->add(new Independent2Command());

$application->run();