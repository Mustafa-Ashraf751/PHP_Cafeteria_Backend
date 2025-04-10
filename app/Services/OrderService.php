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

    // إنشاء طلب جديد
    public function createOrder($userId, $room, $totalPrice, $note = null)
    {
        try {
            // إضافة الطلب باستخدام نموذج Order
            $this->orderModel->createOrder($userId, $room, $totalPrice, $note);
            return ['status' => 'success', 'message' => 'Order created successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to create order: ' . $e->getMessage()];
        }
    }

    // استرجاع جميع الطلبات
    public function getAllOrders()
    {
        try {
            return $this->orderModel->getAllOrders();
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to fetch orders: ' . $e->getMessage()];
        }
    }

    // استرجاع طلب بناءً على الـ ID
    public function getOrderById($id)
    {
        try {
            return $this->orderModel->getOrderById($id);
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to fetch order: ' . $e->getMessage()];
        }
    }

    // تحديث حالة الطلب
    public function updateOrderStatus($id, $status)
    {
        try {
            $this->orderModel->updateOrderStatus($id, $status);
            return ['status' => 'success', 'message' => 'Order status updated successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to update order status: ' . $e->getMessage()];
        }
    }

    // حذف الطلب
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
