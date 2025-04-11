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
    public function createOrder($userId, $roomId, $totalAmount, $notes = null)
    {
        try {
            // add order by use Model Order
            $orderId = $this->orderModel->createOrder($userId, $roomId, $totalAmount, $notes);

            if ($orderId) {
                return ['status' => 'success', 'message' => 'Order created successfully from if().', 'orderId' => $orderId];
            } else {
                return ['status' => 'error', 'message' => 'Failed to create order in database.'];
            }
          
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to create order: ' . $e->getMessage()];
        }
    }

    // get all orders
    public function getAllOrders()
    {
        try {
            return $this->orderModel->getAllOrders();
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to fetch orders: ' . $e->getMessage()];
        }
    }

    // get order by id
    public function getOrderById($id)
    {
        try {
            return $this->orderModel->getOrderById($id);
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to fetch order: ' . $e->getMessage()];
        }
    }

    // update order status
    public function updateOrderStatus($id, $orderStatus)
    {
        try {
            $this->orderModel->updateOrderStatus($id, $orderStatus);
            return ['status' => 'success', 'message' => 'Order status updated successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to update order status: ' . $e->getMessage()];
        }
    }

    // cancel order (replaces delete)
    public function cancelOrder($id)
    {
        try {
            $this->orderModel->cancelOrder($id);
            return ['status' => 'success', 'message' => 'Order cancelled successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to cancel order: ' . $e->getMessage()];
        }
    }
}
