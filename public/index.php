<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
error_log("Script started");

require_once __DIR__ . '/../vendor/autoload.php';

error_log("Autoloader initialized");

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

error_log("Environment variables loaded");

use App\Routers\Router;
use App\Controllers\ProductController;
use App\Controllers\UserController;

$router = new Router();

// Define product routes
$router->get('/products', [ProductController::class, 'getAllProducts']);
$router->get('/products/{id}', [ProductController::class, 'getProductById']);
$router->post('/products', [ProductController::class, 'addProduct']);
$router->patch('/products/{id}', [ProductController::class, 'updateProduct']);
$router->delete('/products/{id}', [ProductController::class, 'deleteProduct']);

// Define routes for user management
$router->get('/users', [UserController::class, 'index']);
$router->get('/users/{id}', [UserController::class, 'show']);
$router->post('/users', [UserController::class, 'store']);
$router->patch('/users/{id}', [UserController::class, 'update']);
$router->delete('/users/{id}', [UserController::class, 'delete']);

$router->run();
