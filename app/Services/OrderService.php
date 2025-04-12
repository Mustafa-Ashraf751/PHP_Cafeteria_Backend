<?php

namespace App\Services;

use App\Models\Order;
use Exception;

class OrderService
{
    private $orderModel;

    public function __construct()
    {
        $this->orderModel = new Order();
    }

    // create order
    public function createOrder($userId, $roomId, $totalAmount, $notes ,$orderItems)
    {
        try {
            if (empty($userId) || empty($roomId) || empty($totalAmount)) {
                return ['status' => 'error', 'message' => 'Invalid order data.'];
            }

            // add order by use Model Order
            $orderId = $this->orderModel->createOrder($userId, $roomId, $totalAmount, $notes,$orderItems);

            if ($orderId) {
                return ['status' => 'success', 'message' => 'Order created successfully from if().', 'orderId' => $orderId];
            } else {
                return ['status' => 'error', 'message' => 'Failed to create order in database.'];
            }
          
        } catch (Exception $e) {
            error_log("Order Items received in ServiceOrder: " . print_r($orderItems, true));

            return ['status' => 'error', 'message' => 'Failed to create order: ' . $e->getMessage()];
        }
    }

    // get all orders
    public function getAllOrders()
    {
        try {
            $orders = $this->orderModel->getAllOrders();
    
            if (!$orders) {
                return ['status' => 'error', 'message' => 'No orders found'];
            }
    
            return ['status' => 'success', 'orders' => $orders];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to fetch orders: ' . $e->getMessage()];
        }
    }

    // get order by id
    public function getOrderById($id)
    {
        try {
            $order = $this->orderModel->getOrderById($id);
            
            // Check if the order exists
            if (isset($order['status']) && $order['status'] === 'error') {
                return null;
            }
    
            return $order;
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to fetch order'];
        }
    }

    // update order status
   public function updateOrderStatus($id, $orderStatus)
    {
         try {
        $order = $this->orderModel->getOrderById($id);
         if (!$order) {
            return ['status' => 'error', 'message' => 'Order not found.'];
        }

            $this->orderModel->updateOrderStatus($id, $orderStatus);
         return ['status' => 'success', 'message' => 'Order status updated successfully.'];
         } catch (Exception $e) {
         return ['status' => 'error', 'message' => 'Failed to update order status: ' . $e->getMessage()];
         }
    }

    public function getOrderDetails($orderId)
{
    try {
        
        if (empty($orderId) || !is_numeric($orderId) || $orderId <= 0) {
            return ['status' => 'error', 'message' => 'Invalid order ID'];
        }

       
        $orderDetails = $this->orderModel->getOrderDetails($orderId);
        
        if (!$orderDetails || isset($orderDetails['status']) && $orderDetails['status'] === 'error') {
            return ['status' => 'error', 'message' => 'Order details not found'];
        }

        return ['status' => 'success', 'orderDetails' => $orderDetails];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'Failed to fetch order details: ' . $e->getMessage()];
    }
}


    // cancel order (replaces delete)
    public function cancelOrder($id)
    {
        try {
            $order = $this->orderModel->getOrderById($id);
            if (!$order) {
                return ['status' => 'error', 'message' => 'Order not found.'];
            }
    
            $this->orderModel->cancelOrder($id);
            return ['status' => 'success', 'message' => 'Order cancelled successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to cancel order: ' . $e->getMessage()];
        }
    }
}
