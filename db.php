<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$capsule = new Capsule;
$capsule->addConnection([
    'driver' => $_ENV['DB_CONNECTION'],
    'host' => $_ENV['DB_HOST'],
    'database' => $_ENV['DB_DATABASE'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => $_ENV['CHARSET'],
    'collation' => $_ENV['COLLATION'],
    'prefix' => $_ENV['PREFIX'],
]);

$capsule->setEventDispatcher(new Dispatcher(new Container));
// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();
// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();