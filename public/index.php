<?php

use DI\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\Csrf\Guard;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
AppFactory::setContainer($container);
$container->set('view', function () {
    $view = Twig::create(__DIR__ . '/../src/Templates', ['cache' => false]);
    return $view;
});
$settings = require __DIR__ . '/../src/settings.php';
$container->set('settings', $settings());

// logs
$logger = new Logger('app');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG));
// set logger in container
$container->set('logger', $logger);

$app = AppFactory::create();

session_start();
$container->set('csrf', function (){
    $csrf = new Guard(new ResponseFactory());
    return $csrf;
});
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