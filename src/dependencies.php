<?php

use App\Controllers\TodoController;
use DI\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\Csrf\Guard;
use Slim\Views\Twig;

// Set up the container
$container = new Container();

// Set up view dependency
$container->set('view', function () {
    $view = Twig::create(__DIR__ . '/../src/Templates', ['cache' => false]);
    return $view;
});

// Set up settings
$settings = require __DIR__ . '/../src/settings.php';
$container->set('settings', $settings());

// Set up logger
$logger = new Logger('app');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG));
$container->set('logger', $logger);

// Set up TodoController
$container->set(TodoController::class, function ($container) {
    return new TodoController(
        $container->get('view'),
        $container->get('csrf'),
        $container->get('logger')
    );
});

// Set up CSRF protection
$container->set('csrf', function () {
    $csrf = new Guard(new \Slim\Psr7\Factory\ResponseFactory());
    return $csrf;
});

return $container;
