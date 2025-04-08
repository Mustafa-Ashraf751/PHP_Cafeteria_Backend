<?php
require_once(__DIR__ . '/../Models/Order.php');


class OrderController
{
    private $orderModel;

    public function __construct()
    {
        $this->orderModel = new Order();
    }

    public function createOrder($data)
    {
        // تحقق من وجود الحقول المطلوبة
        if (!isset($data["user_id"]) || !isset($data["items"]) || !isset($data["total_price"])) {
            return ["status" => "error", "message" => "Missing required fields"];
        }
        var_dump($data);
        // إدخال الطلب في جدول orders
        $orderId = $this->orderModel->createOrder(
            $data["user_id"],
            $data["total_price"],
            $data["notes"] ?? "",
            $data["status"] ?? "Processing"
        );
    
        // إدخال كل منتج في جدول order_items
        foreach ($data["items"] as $item) {
            var_dump($item);
            $this->orderModel->addOrderItem($orderId, $item["product_id"], $item["quantity"]);
        }
    
        return ["status" => "success", "message" => "Order created successfully"];
    }
    
}
?>
