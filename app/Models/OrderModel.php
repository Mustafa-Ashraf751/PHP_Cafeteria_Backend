<?php

namespace App\Models;

use PDO;
use PDOException;
use Exception;
use App\Helpers\Query\OrderQueryHelper;
use DateTime;

class OrderModel
{
	private $db;
	private $tableName = 'orders';

	public function __construct()
	{
		$this->db = DataBase::getDBConnection();
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function getAllOrders($page = 1, $perPage = 6, $orderField = 'created_at', $orderSort = 'ASC')
	{
		try {
			OrderQueryHelper::validateOrderSort($orderSort);
			OrderQueryHelper::validateOrderField($orderField);

			$offset = ($page - 1) * $perPage;

			$query = "SELECT * FROM $this->tableName ORDER BY $orderField $orderSort LIMIT :limit OFFSET :offset";
			$stmt = $this->db->prepare($query);

			$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();

			$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$totalCount = OrderQueryHelper::getOrderTableCount($this->db, $this->tableName);


			return [
				'data' => $orders,
				'pagination' => OrderQueryHelper::buildOrderPaginationMeta($totalCount, $page, $perPage, $offset),
			];
		} catch (PDOException $e) {
			throw new Exception("Error fetching users data" . $e->getMessage());
		}
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
			$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$totalAmount = 0;
			foreach ($orders as $order) {
				$totalAmount += (float) $order['total_amount'];
			}

			return [
				'orders' => $orders,
				'count' => count($orders),
				'total_amount' => $totalAmount
			];
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

	public function getUsersWithOrders($startDate = null, $endDate = null)
	{
		try {
			// Use LEFT JOIN but require at least one order
			$sql = "SELECT u.id as user_id, u.fullName, u.email, u.role, 
                  COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent 
                  FROM users u 
                  LEFT JOIN orders o ON u.id = o.user_id
                  WHERE o.id IS NOT NULL";

			$params = [];

			if ($startDate) {
				$sql .= " AND o.created_at >= :start_date";
				$params[':start_date'] = date('Y-m-d H:i:s', strtotime($startDate . ' 00:00:00'));
			}

			if ($endDate) {
				$sql .= " AND o.created_at <= :end_date";
				$params[':end_date'] = date('Y-m-d H:i:s', strtotime($endDate . ' 23:59:59'));
			}

			$sql .= " GROUP BY u.id ORDER BY order_count DESC";

			$stmt = $this->db->prepare($sql);

			foreach ($params as $param => $value) {
				$stmt->bindValue($param, $value, PDO::PARAM_STR);
			}

			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			error_log('Error in getUsersWithOrders: ' . $e->getMessage());
			throw new Exception('Error fetching users with orders');
		}
	}

	public function getAllUsersWithOrderSummary($page = 1, $perPage = 10, $startDate = null, $endDate = null)
	{
		try {
			// Calculate offset for pagination
			$page = max(1, (int) $page);
			$perPage = max(1, min(100, (int) $perPage));
			$offset = ($page - 1) * $perPage;

			// Count total number of matching users first (for pagination metadata)
			$countSql = "SELECT COUNT(DISTINCT u.id) as total FROM users u 
                      LEFT JOIN orders o ON u.id = o.user_id
                      WHERE o.id IS NOT NULL";

			$countParams = [];

			if ($startDate) {
				$countSql .= " AND o.created_at >= :start_date";
				$countParams[':start_date'] = date('Y-m-d H:i:s', strtotime($startDate . ' 00:00:00'));
			}

			if ($endDate) {
				$countSql .= " AND o.created_at <= :end_date";
				$countParams[':end_date'] = date('Y-m-d H:i:s', strtotime($endDate . ' 23:59:59'));
			}

			$countStmt = $this->db->prepare($countSql);

			foreach ($countParams as $param => $value) {
				$countStmt->bindValue($param, $value, PDO::PARAM_STR);
			}

			$countStmt->execute();
			$totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
			$totalPages = ceil($totalCount / $perPage);

			// Main query with pagination
			$sql = "SELECT u.id as user_id, u.fullName, u.email, u.role, 
                  COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent 
                  FROM users u 
                  LEFT JOIN orders o ON u.id = o.user_id
                  WHERE o.id IS NOT NULL";

			$params = [];

			if ($startDate) {
				$sql .= " AND o.created_at >= :start_date";
				$params[':start_date'] = date('Y-m-d H:i:s', strtotime($startDate . ' 00:00:00'));
			}

			if ($endDate) {
				$sql .= " AND o.created_at <= :end_date";
				$params[':end_date'] = date('Y-m-d H:i:s', strtotime($endDate . ' 23:59:59'));
			}

			$sql .= " GROUP BY u.id ORDER BY order_count DESC LIMIT :limit OFFSET :offset";

			$stmt = $this->db->prepare($sql);

			// Bind all parameters
			foreach ($params as $param => $value) {
				$stmt->bindValue($param, $value, PDO::PARAM_STR);
			}

			// Bind pagination parameters
			$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
			$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

			$stmt->execute();
			$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// Format the results
			foreach ($users as &$user) {
				$user['user_id'] = (int) $user['user_id'];
				$user['order_count'] = (int) $user['order_count'];
				$user['total_spent'] = (float) $user['total_spent'];
			}

			// Return data with pagination metadata
			return [
				'data' => $users,
				'pagination' => [
					'page' => $page,
					'per_page' => $perPage,
					'total_items' => $totalCount,
					'total_pages' => $totalPages,
					'has_next' => $page < $totalPages,
					'has_prev' => $page > 1
				]
			];
		} catch (PDOException $e) {
			error_log('Error in getAllUsersWithOrderSummary: ' . $e->getMessage());
			throw new Exception('Error fetching user order summary: ' . $e->getMessage());
		}
	}

	public function getOrderInfo($orderId)
	{
		try {
			$sql = "SELECT p.id, oi.quantity, oi.price_at_order,  p.name, p.image
									FROM $this->tableName  AS o
									JOIN order_items AS oi ON o.id = oi.order_id
									JOIN products AS p ON oi.product_id = p.id
									WHERE o.id = :id
									";

			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id', $orderId, PDO::PARAM_INT);
			$stmt->execute();

			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			error_log('Error in getOrderInfo: ' . $e->getMessage());
			throw new Exception('Error fetching order info: ' . $e->getMessage());
		}
	}

	public function getUserOfOrder($orderId)
	{
		try {
			$sql = "SELECT o.user_id
					FROM $this->tableName  AS o
					WHERE o.id = :id
					LIMIT 1
					";

			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':id', $orderId, PDO::PARAM_INT);
			$stmt->execute();

			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			error_log('Error in getOrderInfo: ' . $e->getMessage());
			throw new Exception('Error fetching order info: ' . $e->getMessage());
		}
	}

}