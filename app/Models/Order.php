<?php

namespace App\Models;

use PDO;
use PDOException;
use Exception;

class Order
{
    private $db;

    public function __construct()
    {
        $this->db = DataBase::getDBConnection();
    }

    public function createOrder($userId, $roomId, $totalAmount, $notes = null)
    {
        try {
            $sql = "INSERT INTO orders (user_id, room_id, total_amount, notes, order_status) 
                    VALUES (:user_id, :room_id, :total_amount, :notes, :order_status)";
            $stmt = $this->db->prepare($sql);
            if ($stmt->execute([$userId, $roomId, $totalAmount, $notes])) {
                echo json_encode(["status" => "success", "message" => "Order created successfully."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to insert order", "error" => $stmt->errorInfo()]);
            }
    
            
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
            $stmt->bindParam(':total_amount', $totalAmount);
            $stmt->bindParam(':notes', $notes, $notes !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            
            $orderStatus = 'pending';
            $stmt->bindParam(':order_status', $orderStatus, PDO::PARAM_STR);
            
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId(); 
            } else {
                error_log("Error inserting order: " . implode(", ", $stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error in createOrder: " . $e->getMessage());
            return false;
        }
    }

    public function getAllOrders()
    {
        try {
            $sql = "SELECT * FROM orders ORDER BY created_at DESC"; 
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAllOrders: " . $e->getMessage());
            return [];
        }
    }

    public function getOrderById($id)
    {
        try {
            $sql = "SELECT * FROM orders WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getOrderById: " . $e->getMessage());
            return null;
        }
    }

    public function updateOrderStatus($id, $orderStatus)
    {
        try {
            $sql = "UPDATE orders SET order_status = :order_status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':order_status', $orderStatus, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in updateOrderStatus: " . $e->getMessage());
            return false;
        }
    }

    public function cancelOrder($id)
    {
        try {
            $sql = "UPDATE orders SET order_status = 'Cancelled' WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in cancelOrder: " . $e->getMessage());
            return false;
        }
    }
}
