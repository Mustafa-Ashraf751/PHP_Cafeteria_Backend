<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Dotenv\Dotenv;
use Cloudinary\Api\Upload\UploadApi;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

Configuration::instance([
    'cloud' => [
        'cloud_name' => $_ENV['CLOUD_NAME'],
        'api_key'    => $_ENV['API_KEY'],
        'api_secret' => $_ENV['API_SECRET'],
    ],
    'url' => [
        'secure' => true
    ]
]);

function uploadToCloudinary($filePath)
{
    if (!file_exists($filePath)) {
        die("Error: File not found at path: $filePath");
    }

    $uploadApi = new UploadApi();
    $result = $uploadApi->upload($filePath, [
        'folder' => 'php_cafeteria' 
    ]);

    return $result['secure_url']; 
}

