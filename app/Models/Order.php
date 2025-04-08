<?php
require_once "DataBase.php";

class Order
{
    private $db;

    public function __construct()
    {
        $this->db = DataBase::getDBConnection();
    }

    public function createOrder($user_id, $status, $total_price, $notes)
    {
        try {
            $query = "INSERT INTO orders (user_id,total_price, notes, status, created_at) VALUES (:user_id, :items, :total_price, :notes, 'Processing', NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ":user_id" => $user_id,
                ":total_price" => $total_price,
                ":notes" => $notes,
                ":status" => $status,
            ]);
            return ["status" => "success", "message" => "Order created successfully!"];
        } catch (PDOException $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
        return $this->db->lastInsertId();
    }

    public function addOrderItem($orderId, $productId, $quantity)
    {
    // SQL for insert item into order_items
    $query = "INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmt = $this->db->prepare($query);
    $stmt->execute([$orderId, $productId, $quantity]);
    }

}
?>
