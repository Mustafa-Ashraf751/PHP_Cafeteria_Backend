<?php

namespace App\Models;

use PDO;

class Order
{
    private $db;

    public function __construct()
    {
        $this->db = DataBase::getDBConnection();
    }

    // إضافة طلب جديد
    public function createOrder($userId, $room, $totalPrice, $note = null)
    {
        $sql = "INSERT INTO orders (user_id, room, total_price, note, status) VALUES (:user_id, :room, :total_price, :note, :status)";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':room', $room);
        $stmt->bindParam(':total_price', $totalPrice);
        $stmt->bindParam(':note', $note);
        $status = 'Processing';  // حالة الطلب عند الإنشاء
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    }

    // الحصول على جميع الطلبات
    public function getAllOrders()
    {
        $sql = "SELECT * FROM orders ORDER BY created_at DESC"; // ترتيب حسب التاريخ (الأحدث أولاً)
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // الحصول على طلب بواسطة ID
    public function getOrderById($id)
    {
        $sql = "SELECT * FROM orders WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // تحديث حالة الطلب (مثلاً: من Processing إلى Done)
    public function updateOrderStatus($id, $status)
    {
        $sql = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    // حذف طلب
    public function deleteOrder($id)
    {
        $sql = "DELETE FROM orders WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
