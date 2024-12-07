<?php

use Illuminate\Database\Capsule\Manager as Capsule;
return function () {
    $env = parse_ini_file(__DIR__ . "/../.env");

    $capsule = new Capsule();
    $capsule->addConnection([
        'driver' => $env['DRIVER'],
        'host' => $env['DB_HOST'],
        'database' => $env['DB'],
        'username' => $env['DB_USERNAME'],
        'password' => $env['DB_PASS'],
        'charset' => $env['DB_CHARSET'],
        'collation' => $env['DB_COLLATION'],
        'prefix' => $env['DB_PREFIX'],
    ]);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();
};
