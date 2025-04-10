<?php

namespace App;


use App\Routers\Router;
use App\Controllers\UserController;
use App\Controllers\OrderController;
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

// Define routes for orders
$router->get('/orders', OrderController::class, 'index');  // List all orders
$router->get('/orders/{id}', OrderController::class, 'show');  // Get a single order by ID
$router->post('/orders', OrderController::class, 'store');  // Create a new order
$router->patch('/orders/{id}/status', OrderController::class, 'updateStatus');  // Update order status
$router->delete('/orders/{id}', OrderController::class, 'delete');  // Delete an order



// Handle 404 errors
$router->setNotFoundHandler(function () {
  http_response_code(404);
  echo json_encode(['error' => 'Resource not found']);
});

return $router;
