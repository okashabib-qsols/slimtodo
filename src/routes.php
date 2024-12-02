<?php

return function ($app) {

    // Todos
    $app->get('/todos', "App\Controllers\TodoController:index");
    $app->get('/todos/{id}', "App\Controllers\TodoController:show");
    $app->post('/todos', "App\Controllers\TodoController:store");
    $app->put('/todos/{id}', "App\Controllers\TodoController:update");
    $app->delete('/todos/{id}', "App\Controllers\TodoController:delete");

};
