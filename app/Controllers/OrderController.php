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

    // دالة لإنشاء طلب جديد
    public function store($params)
    {
        // الحصول على البيانات من الطلب (مثل المستخدم، الغرفة، والسعر)
        $userId = $params['user_id'];  // فرضًا أن هذه البيانات تأتي من الـ request
        $room = $params['room'];
        $totalPrice = $params['total_price'];
        $note = $params['note'] ?? null;  // ملاحظة اختيارية

        // الاتصال بخدمة الطلبات
        $response = $this->orderService->createOrder($userId, $room, $totalPrice, $note);

        // إرسال الاستجابة للمستخدم
        echo json_encode($response);
    }

    // دالة لاسترجاع كل الطلبات
    public function index()
    {
        // استرجاع كل الطلبات
        $orders = $this->orderService->getAllOrders();

        // إرسال الطلبات في استجابة
        echo json_encode($orders);
    }

    // دالة لاسترجاع طلب معين باستخدام الـ ID
    public function show($params)
    {
        $orderId = $params['id'];

        // استرجاع الطلب من الخدمة
        $order = $this->orderService->getOrderById($orderId);

        // إرسال الطلب في استجابة
        echo json_encode($order);
    }

    // دالة لتحديث حالة الطلب
    public function updateStatus($params)
    {
        $orderId = $params['id'];
        $status = $params['status'];

        // تحديث حالة الطلب من خلال الخدمة
        $response = $this->orderService->updateOrderStatus($orderId, $status);

        // إرسال الاستجابة
        echo json_encode($response);
    }

    // دالة لحذف طلب
    public function delete($params)
    {
        $orderId = $params['id'];

        // حذف الطلب من خلال الخدمة
        $response = $this->orderService->deleteOrder($orderId);

        // إرسال الاستجابة
        echo json_encode($response);
    }
}
