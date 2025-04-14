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

    public function createOrder($userId, $roomId, $totalAmount, $notes, $orderItems)
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
            
            // Logging for debugging
            error_log("Attempting to insert order with data: user_id=$userId, room_id=$roomId, total_amount=$totalAmount, notes=$notes");
            error_log("Inserting order with user_id = $userId, room_id = $roomId, total_amount = $totalAmount, notes = $notes");

            if ($stmt->execute()) {
                $orderId = $this->db->lastInsertId(); 
                
                // Now insert the order items into order_details
                foreach ($orderItems as $item) {
                    if (!isset($item['product_id'], $item['quantity'], $item['price'])) {
                        throw new Exception('Missing item details in order_items');
                    }                    
                    $productId = $item['product_id'];
                    $quantity = $item['quantity'];
                    $price = $item['price'];
    
                    $sqlDetails = "INSERT INTO order_details (order_id, product_id, quantity, price) 
                                   VALUES (:order_id, :product_id, :quantity, :price)";
                    $stmtDetails = $this->db->prepare($sqlDetails);
                    $stmtDetails->bindParam(':order_id', $orderId, PDO::PARAM_INT);
                    $stmtDetails->bindParam(':product_id', $productId, PDO::PARAM_INT);
                    $stmtDetails->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                    $stmtDetails->bindParam(':price', $price, PDO::PARAM_STR);
    
                    // Log details query for debugging
                    error_log("Inserting order details: order_id=$orderId, product_id=$productId, quantity=$quantity, price=$price");
    
                    $stmtDetails->execute();
                }

                return $orderId; 

            } else {
                // Log error in case of failure
                
                $errorInfo = $stmt->errorInfo();
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error inserting order: ' . implode(' | ', $errorInfo)
                ]);
                return false;
            }
        } catch (PDOException $e) {
            // Log the exception error
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

public function getOrderDetails($orderId)
{
    try {
       
        $orderId = intval($orderId);

        if (!is_numeric($orderId) || $orderId <= 0) {
            return ['status' => 'error', 'message' => 'Invalid order ID'];
        }

       
        $sql = "SELECT od.order_id, od.product_id, od.quantity, od.price, p.name AS product_name 
                FROM order_details od 
                JOIN products p ON od.product_id = p.id 
                WHERE od.order_id = :order_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);

        error_log("SQL Query: " . $sql); 
        $stmt->execute();
        
        
        $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$orderDetails) {
            return ['status' => 'error', 'message' => 'Order details not found'];
        }

        return $orderDetails;

    } catch (PDOException $e) {
        error_log("Error in getOrderDetails: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Database error occurred'];
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
