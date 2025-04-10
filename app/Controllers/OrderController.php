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
    public function store($params)
    {
        // get data from request
        $userId = $params['user_id'];  
        $room = $params['room'];
        $totalPrice = $params['total_price'];
        $note = $params['note'] ?? null;  

        // connect to the service to create the order
        $response = $this->orderService->createOrder($userId, $room, $totalPrice, $note);

        // send the response
        echo json_encode($response);
    }

    // method to get all orders
    public function index()
    {
       
        $orders = $this->orderService->getAllOrders();

       
        echo json_encode($orders);
    }

  
    public function show($params)
    {
        $orderId = $params['id'];

        
        $order = $this->orderService->getOrderById($orderId);

        
        echo json_encode($order);
    }

   
    public function updateStatus($params)
    {
        $orderId = $params['id'];
        $status = $params['status'];

        
        $response = $this->orderService->updateOrderStatus($orderId, $status);

        
        echo json_encode($response);
    }

   
    public function delete($params)
    {
        $orderId = $params['id'];

       
        $response = $this->orderService->deleteOrder($orderId);

        
        echo json_encode($response);
    }
}
