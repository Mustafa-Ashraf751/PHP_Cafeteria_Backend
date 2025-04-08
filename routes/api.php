<?php
require_once __DIR__ . '/../controllers/OrderController.php';

$orderController = new OrderController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'create_order') {
    $orderController->createOrder();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'get_orders') {
    $orderController->getOrders();
}
?>
