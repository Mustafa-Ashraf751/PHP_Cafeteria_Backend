<?php

namespace App;

use App\Controllers\CategoryController;
use App\Controllers\ProductController;
use App\Routers\Router;
use App\Controllers\UserController;
use App\Services\CategoryService;

$router = new Router('/PHP_Cafeteria_Backend/public');

// Define routes for user management
$router->get('/users', UserController::class, 'index');
$router->get('/users/{id}', UserController::class, 'show');
$router->post('/users', UserController::class, 'store');
$router->patch('/users/{id}', UserController::class, 'update');
$router->delete('/users/{id}', UserController::class, 'delete');

// Define routes for product management
$router->get('/products', ProductController::class, 'getAllProducts');
$router->get('/products/{id}', ProductController::class, 'getProductById');
$router->post('/products', ProductController::class, 'addProduct');
$router->patch('/products/{id}', ProductController::class, 'updateProduct');
$router->delete('/products/{id}', ProductController::class, 'deleteProduct');
// Define routes for category management
$router->get('/categories', CategoryController::class, 'getCategories');
$router->post('/categories', CategoryController::class, 'addCategory');
$router->put('/categories/{id}', CategoryController::class, 'updateCategory');
$router->delete('/categories/{id:\d+}', CategoryController::class, 'deleteCategoryById');

// Authentication routes
// $router->post('/login', UserController::class, 'login');
// $router->post('/register', UserController::class, 'register');

// Handle 404 errors
$router->setNotFoundHandler(function () {
  http_response_code(404);
  echo json_encode(['error' => 'Resource not found']);
});

return $router;