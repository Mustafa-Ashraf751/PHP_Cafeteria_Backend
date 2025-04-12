<?php

namespace App\Models;

use PDO;
use PDOException;
use Exception;
use DateTime;

class Order
{
    private $db;

    public function __construct()
    {
        $this->db = DataBase::getDBConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createOrder($userId, $roomId, $totalAmount, $notes = null)
    {
        try {
            $sql = "INSERT INTO orders (user_id, room_id, total_amount, notes, order_status) 
                   VALUES (:user_id, :room_id, :total_amount, :notes, :order_status)";
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
            $stmt->bindParam(':total_amount', $totalAmount, PDO::PARAM_STR);
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

    public function getOrdersByUserAndDateRange($userId, $startDate = null, $endDate = null)
    {
        try {
            $sql = "SELECT * FROM orders WHERE user_id=:user_id";
            $params = [':user_id' => $userId];

            if ($startDate) {
                // Validate and format start date
                $startDateTime = DateTime::createFromFormat('Y-m-d', $startDate);
                if (!$startDateTime) {
                    throw new Exception('Invalid start date format. Use YYYY-MM-DD');
                }
                $sql .= " AND created_at >= :start_date";
                $params[':start_date'] = $startDateTime->format('Y-m-d 00:00:00');
            }

            if ($endDate) {
                // Validate and format end date
                $endDateTime = DateTime::createFromFormat('Y-m-d', $endDate);
                if (!$endDateTime) {
                    throw new Exception('Invalid end date format. Use YYYY-MM-DD');
                }
                $sql .= " AND created_at <= :end_date";
                $params[':end_date'] = $endDateTime->format('Y-m-d 23:59:59');
            }
            $sql .= " ORDER BY created_at DESC";

            $stmt = $this->db->prepare($sql);

            foreach ($params as $param => $value) {
                //Dynamic choose the type of param
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching users orders" . $e->getMessage());
        }
    }

    public function getOrderById($id)
    {
        try {
            $id = intval($id);

            if (!is_numeric($id) || $id <= 0) {
                return ['status' => 'error', 'message' => 'Invalid order ID'];
            }

            $sql = "SELECT * FROM orders WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            error_log("SQL Query: " . $sql);
            $stmt->execute();

            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            // var_dump($order);
            // Check if the order exists
            if (!$order || !is_array($order)) {
                return ['status' => 'error', 'message' => 'Order not found'];
            }
            if (empty($order['id'])) {
                return ['status' => 'error', 'message' => 'Invalid order data'];
            }

            return $order;
        } catch (PDOException $e) {
            error_log("Error in getOrderById: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Database error occurred'];
        }
    }


    public function updateOrderStatus($id, $orderStatus)
    {
        try {
            if (!$this->getOrderById($id)) {
                return false;
            }

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
            if (!$this->getOrderById($id)) {
                return false;
            }

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
