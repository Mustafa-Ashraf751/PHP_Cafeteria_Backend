<?php
header("Content-Type: application/json");
require_once ( __DIR__ . '/../app/Controllers/OrderController.php') ;

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    var_dump($data);
    $orderController = new OrderController();
    $response = $orderController->createOrder($data);
    echo json_encode($response);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
