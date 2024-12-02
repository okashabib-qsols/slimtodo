<?php

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
AppFactory::setContainer($container);
$container->set('view', function () {
    $view = Twig::create(__DIR__ . '/../src/templates', ['cache' => false]);
    return $view;
});
$settings = require __DIR__ . '/../src/settings.php';
$container->set('settings', $settings());

$app = AppFactory::create();

// echo "<pre>";
// var_dump($container);
// echo "</pre>";

$app->add(TwigMiddleware::createFromContainer($app));

// Register Middleware
$middleware = require __DIR__ . '/../src/middleware.php';
$middleware($app);

// Routes Register
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

// App start
$app->run();