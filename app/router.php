<?php

namespace App;

use App\Controllers\CategoryController;
use App\Controllers\ProductController;
use App\Routers\Router;
use App\Controllers\UserController;

$router = new Router("/ITI/PHP_Cafeteria_Backend/public");

//Public routes
$router->post('/login', UserController::class, 'login');


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

// Admin only routes
$router->get('/admin/users', UserController::class, 'index');  
$router->post('/admin/users', UserController::class, 'register');
$router->delete('/admin/users/{id}', UserController::class, 'delete'); 

// Handle 404 errors
$router->setNotFoundHandler(function () {
  http_response_code(404);
  echo json_encode(['error' => 'Resource not found']);
});

return $router;
