<?php
// Handle preflight first
// if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
//   header("Access-Control-Allow-Origin: http://localhost:5173");
//   header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
//   header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
//   header("Access-Control-Allow-Credentials: true");
//   header('Content-Type: application/json');
//   exit(0);
// }

// // Regular headers for actual responses
// header("Access-Control-Allow-Origin: http://localhost:5173");
// header("Access-Control-Allow-Credentials: true");
// header('Content-Type: application/json');

// Load dependencies
require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$router = require __DIR__ . '/../app/router.php';
$router->dispatch();