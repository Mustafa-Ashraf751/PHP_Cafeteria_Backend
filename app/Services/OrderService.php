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
    public function createOrder($userId, $room, $totalPrice, $note = null)
    {
        try {
            // add order by use Model Order
            $this->orderModel->createOrder($userId, $room, $totalPrice, $note);
            return ['status' => 'success', 'message' => 'Order created successfully.'];
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
    public function updateOrderStatus($id, $status)
    {
        try {
            $this->orderModel->updateOrderStatus($id, $status);
            return ['status' => 'success', 'message' => 'Order status updated successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to update order status: ' . $e->getMessage()];
        }
    }

    // delete order
    public function deleteOrder($id)
    {
        try {
            $this->orderModel->deleteOrder($id);
            return ['status' => 'success', 'message' => 'Order deleted successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to delete order: ' . $e->getMessage()];
        }
    }
}
