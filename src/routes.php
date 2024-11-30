<?php

return function ($app) {

    // Todos
    $app->get('/todos', "App\Controllers\TodoController:index");
    $app->get('/todos/{id}', "App\Controllers\TodoController:show");
    $app->delete('/todos/{id}', "App\Controllers\TodoController:delete");

};
