<?php
// Set headers at the VERY TOP before any output
// header("Access-Control-Allow-Origin: http://localhost:5173");
// header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");
// header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

// Immediately handle OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Now load dependencies
require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$router = require __DIR__ . '/../app/router.php';
$router->dispatch();