<?php
header("Content-Type: application/json");
require_once (__DIR__ . '/../app/Models/DataBase.php');

try {
    $pdo = DataBase::getDBConnection();
    echo json_encode(["status" => "success", "message" => "Database connected successfully!"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
