<?php

require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// $basePath = '/ITI/PHP_Cafeteria_Backend/public';

$router = require __DIR__ . '/../app/router.php';
// $router->setBasePath($basePath);
$router->dispatch();
