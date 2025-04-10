<?php

namespace App\Controllers;

use App\Services\OrderService;
use Exception;

class OrderController
{
    private $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    // method to create a new order
    public function store()
    {
        // get data from request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        // extract individual values
        $userId = $data['user_id'];
        $roomId = $data['room_id'];  // Changed from 'room' to 'room_id'
        $totalAmount = $data['total_amount'];  // Changed from 'total_price' to 'total_amount'
        $notes = $data['notes'] ?? null;  // Changed from 'note' to 'notes'

        // connect to the service to create the order
        $response = $this->orderService->createOrder($userId, $roomId, $totalAmount, $notes);

        // send the response
        echo json_encode($response);
    }

    // method to get all orders
    public function index()
    {
        $orders = $this->orderService->getAllOrders();

        echo json_encode($orders);
    }

    // method to show a single order
    public function show($params)
    {
        $orderId = $params['id'];

        $order = $this->orderService->getOrderById($orderId);

        echo json_encode($order);
    }

    // method to update order status
    public function updateStatus($params)
    {
        $orderId = $params['id'];
        $status = $params['status'];

        $response = $this->orderService->updateOrderStatus($orderId, $status);

        echo json_encode($response);
    }

    // method to cancel an order (replaces delete)
    public function cancel($params)
    {
        $orderId = $params['id'];

        // connect to the service to cancel the order
        $response = $this->orderService->cancelOrder($orderId);

        // send the response
        echo json_encode($response);
    }
}
