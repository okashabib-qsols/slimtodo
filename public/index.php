<?php

use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../src/dependencies.php';
AppFactory::setContainer($container);

$app = AppFactory::create();

// Starting session for csrf
session_start();

$app->add($container->get('csrf'));

$app->add(TwigMiddleware::createFromContainer($app));

// Register Middleware
$middleware = require __DIR__ . '/../src/middleware.php';
$middleware($app);

// Routes Register
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

// App start
$app->run();