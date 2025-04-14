<?php

namespace App;

use App\Controllers\CategoryController;
use App\Controllers\OrderController;
use App\Controllers\ProductController;
use App\Routers\Router;
use App\Controllers\UserController;

$router = new Router('/PHP_Cafeteria_Backend/public');

use App\Services\CategoryService;

$router = new Router('/PHP_Cafeteria_Backend/public');

//Public routes
$router->post('/login', UserController::class, 'login');


// Define routes for user self management
$router->get('/users', UserController::class, 'index');
$router->get('/users/{id}', UserController::class, 'show');
$router->post('/users', UserController::class, 'store');
$router->patch('/users/{id}', UserController::class, 'update');
$router->delete('/users/{id}', UserController::class, 'delete');

// Define routes for product management
$router->get('/products', ProductController::class, 'getAllProducts');
$router->get('/products/{id}', ProductController::class, 'getProductById');
$router->post('/products', ProductController::class, 'addProduct');
$router->post('/products/{id}', ProductController::class, 'updateProduct');
$router->delete('/products/{id}', ProductController::class, 'deleteProduct');

// Define routes for category management
$router->get('/categories', CategoryController::class, 'getCategories');
$router->post('/categories', CategoryController::class, 'addCategory');
$router->put('/categories/{id}', CategoryController::class, 'updateCategory');
$router->delete('/categories/{id:\d+}', CategoryController::class, 'deleteCategoryById');

// Admin only routes
$router->get('/admin/users', UserController::class, 'index');
$router->post('/admin/users', UserController::class, 'register');
$router->delete('/admin/users/{id}', UserController::class, 'delete');

// Define routes for orders
$router->get('/orders', OrderController::class, 'index');  // List all orders
$router->get('/users/{userId}/orders', OrderController::class, 'getUserOrders'); // Get the user order with date range
$router->get('/users-with-orders', OrderController::class, 'getUsersWithOrders'); // Get all users who make orders
$router->get('/orders/{id}', OrderController::class, 'show'); // Get a single order by ID
$router->post('/orders', OrderController::class, 'store');  // Create a new order
$router->patch('/orders/{id}/status', OrderController::class, 'updateStatus');  // Update order status
$router->patch('/orders/{id}/cancel', OrderController::class, 'cancel');  // Cancel an order (replaces delete)
$router->get('/orders/{id}/info', OrderController::class, 'getOrderInfo');  // Cancel an order (replaces delete)

// Handle 404 errors
$router->setNotFoundHandler(function () {
  http_response_code(404);
  echo json_encode(['error' => 'Resource not found']);
});

return $router;
