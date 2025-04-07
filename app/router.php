<?php

namespace App;


use App\Routers\Router;
use App\Controllers\UserController;

$router = new Router('/PHP_Cafeteria_Backend/public');

// Define routes for user management
$router->get('/users', UserController::class, 'index');
$router->get('/users/{id}', UserController::class, 'show');
$router->post('/users', UserController::class, 'store');
$router->patch('/users/{id}', UserController::class, 'update');
$router->delete('/users/{id}', UserController::class, 'delete');

// Authentication routes
// $router->post('/login', UserController::class, 'login');
// $router->post('/register', UserController::class, 'register');

// Handle 404 errors
$router->setNotFoundHandler(function () {
  http_response_code(404);
  echo json_encode(['error' => 'Resource not found']);
});

return $router;
