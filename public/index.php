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
// Load dependencies
require __DIR__ . '/../vendor/autoload.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');
// Handle CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// Enable error reporting for development
// Uncomment the following lines for debugging

error_reporting(E_ALL);
ini_set('display_errors', 1);



$router = require __DIR__ . '/../app/router.php';
$router->dispatch();