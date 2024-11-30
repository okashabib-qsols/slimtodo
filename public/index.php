<?php

use Slim\Factory\AppFactory;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
AppFactory::setContainer($container);
$settings = require __DIR__ . '/../src/settings.php';
$container->set('settings', $settings());

$app = AppFactory::create();

// Register Middleware
$middleware = require __DIR__ . '/../src/middleware.php';
$middleware($app);

// Routes Register
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

// App start
$app->run();