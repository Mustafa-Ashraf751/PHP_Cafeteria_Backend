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
        // check if data is valid
        if (!isset($data['user_id'], $data['room_id'], $data['total_amount'] , $data['order_items'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            return;
        }
        $orderItems = isset($data['order_items']) ? $data['order_items'] : [];
        if (empty($orderItems)) {
            echo json_encode(['error' => 'Order items are required.']);
            return;
        }
        // extract individual values
        $userId = $data['user_id'];
        $roomId = $data['room_id'];  // Changed from 'room' to 'room_id'
        $totalAmount = $data['total_amount'];  // Changed from 'total_price' to 'total_amount'
        $notes = $data['notes'] ?? null;  // Changed from 'note' to 'notes'
        $orderItems = isset($data['order_items']) ? $data['order_items'] : [];
        // connect to the service to create the order
        

        $response = $this->orderService->createOrder($userId, $roomId, $totalAmount, $notes, $orderItems);
        
        if ($response['status'] === 'success') {
            echo json_encode(['status' => 'success', 'message' => 'Order created successfully.', 'orderId' => $response['orderId']]);
        } else {
            echo json_encode($response);
        }
    }

    // method to get all orders
    public function index()
    {
        $orders = $this->orderService->getAllOrders();
        if ($orders['status'] === 'error') {
            echo json_encode(['status' => 'error', 'message' => $orders['message']]);
            return;
        }
        

        echo json_encode($orders);
    }

    // method to show a single order
    public function show($id) 
    {
        
        if (!is_numeric($id) || $id <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid order ID format"]);
            return;
        }
    
        $order = $this->orderService->getOrderById((int)$id);
        
        if (!$order || (isset($order['status']) && $order['status'] === 'error')) {
            http_response_code(404);
            echo json_encode(["error" => "Order with ID $id not found"]);
            return;
        }
    
        http_response_code(200);
        echo json_encode($order);
    }

    // method to update order status
    public function updateStatus($id)
    {
        // $orderId = $params['id'];
        // $status = $params['status'];
        $data = json_decode(file_get_contents('php://input'), true);

        $order = $this->orderService->getOrderById($id);
        if (!isset($data['status'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing status parameter"]);
            return;
        }

        if (!$order) {
            echo json_encode(["status" => "error", "message" => "Order not found"]);
            return;
        }

       $response = $this->orderService->updateOrderStatus($id, $data['status']);

        echo json_encode($response);
    }

    public function getOrderDetails($orderId)
    {
        if (!is_numeric($orderId) || $orderId <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid order ID format"]);
            return;
        }
    
        
        $result = $this->orderService->getOrderDetails($orderId);
    
        
        if (isset($result['status']) && $result['status'] === 'error') {
            http_response_code(404);
            echo json_encode(["error" => "Order details not found"]);
        } else {
            http_response_code(200);
            echo json_encode($result);
        }
    }
    


    // method to cancel an order (replaces delete)
    public function cancel($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
       // $orderId = $params['id'];

        $order = $this->orderService->getOrderById($id);
        if (!isset($data['status'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing status parameter"]);
            return;
        }
        if (!$order) {
            echo json_encode(["status" => "error", "message" => "Order not found"]);
            return;
        }

        // connect to the service to cancel the order
        $response = $this->orderService->cancelOrder($id);

        // send the response
        echo json_encode($response);
    }
}
