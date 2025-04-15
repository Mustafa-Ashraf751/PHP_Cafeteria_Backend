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

			$orderStatus = 'Processing';
			$stmt->bindParam(':order_status', $orderStatus, PDO::PARAM_STR);

			// Logging for debugging
			error_log("Attempting to insert order with data: user_id=$userId, room_id=$roomId, total_amount=$totalAmount, notes=$notes");
			error_log("Inserting order with user_id = $userId, room_id = $roomId, total_amount = $totalAmount, notes = $notes");

			if ($stmt->execute()) {
				$orderId = $this->db->lastInsertId();

				// Now insert the order items into order_details
				foreach ($orderItems as $item) {
					error_log("Inserting order item: product_id=$item[product_id], quantity=$item[quantity], price=$item[price]");
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



	public function getUsersWithOrders($page = 1, $perPage = 6, $orderField = 'created_at', $orderSort = 'ASC')
	{
		try {
			OrderQueryHelper::validateOrderSort($orderSort);
			// Update validation to handle table prefixes
			OrderQueryHelper::validateOrderField($orderField);

			$offset = ($page - 1) * $perPage;

			$query = "SELECT 
						u.fullName, 
						u.roomNum, 
						u.Ext,
						o.id as order_id, 
						o.created_at, 
						o.total_amount,
						oi.quantity, 
						oi.price
					FROM users u
					INNER JOIN orders o ON u.id = o.user_id
					INNER JOIN order_details oi ON o.id = oi.order_id
					WHERE o.order_status = 'Processing'
					ORDER BY $orderField $orderSort 
					LIMIT :limit OFFSET :offset";

			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();

			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// Get total count for pagination
			$totalCount = OrderQueryHelper::getOrderTableCount($this->db, $this->tableName);

			return [
				'data' => $results,
				'pagination' => OrderQueryHelper::buildOrderPaginationMeta($totalCount, $page, $perPage, $offset),
			];
		} catch (PDOException $e) {
			throw new Exception("Error fetching users data" . $e->getMessage());
		}
	}


	public function getAllUsersWithOrderSummary($page = 1, $perPage = 10, $userId = null, $startDate = null, $endDate = null)
	{
		try {
			$page = max(1, (int) $page);
			$perPage = max(1, min(100, (int) $perPage));
			$offset = ($page - 1) * $perPage;

			// Build WHERE conditions
			$where = [];
			$params = [];

			if ($userId) {
				$where[] = "u.id = :user_id";
				$params[':user_id'] = $userId;
			}
			if ($startDate) {
				$where[] = "o.created_at >= :start_date";
				$params[':start_date'] = date('Y-m-d H:i:s', strtotime($startDate . ' 00:00:00'));
			}
			if ($endDate) {
				$where[] = "o.created_at <= :end_date";
				$params[':end_date'] = date('Y-m-d H:i:s', strtotime($endDate . ' 23:59:59'));
			}

			// Only users with at least one order in the date range
			$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

			// Count total users for pagination
			$countSql = "SELECT COUNT(DISTINCT u.id) as total
						  FROM users u
						  INNER JOIN orders o ON u.id = o.user_id
						  $whereSql";
			$countStmt = $this->db->prepare($countSql);
			foreach ($params as $param => $value) {
				$countStmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
			}
			$countStmt->execute();
			$totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
			$totalPages = ceil($totalCount / $perPage);

			// Main query
			$sql = "SELECT u.id as user_id, u.fullName, u.email, u.role,
							COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent
					 FROM users u
					 INNER JOIN orders o ON u.id = o.user_id
					 $whereSql
					 GROUP BY u.id
					 ORDER BY order_count DESC
					 LIMIT :limit OFFSET :offset";
			$stmt = $this->db->prepare($sql);

			foreach ($params as $param => $value) {
				$stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
			}
			$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
			$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

			$stmt->execute();
			$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ($users as &$user) {
				$user['user_id'] = (int) $user['user_id'];
				$user['order_count'] = (int) $user['order_count'];
				$user['total_spent'] = (float) $user['total_spent'];
			}

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
			$sql = "SELECT p.id, oi.quantity, oi.price,  p.name, p.image
									FROM $this->tableName  AS o
									JOIN order_details AS oi ON o.id = oi.order_id
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
	public function getOrderDetails($orderId)
	{
		try {

			$orderId = intval($orderId);

			if (!is_numeric($orderId) || $orderId <= 0) {
				return ['status' => 'error', 'message' => 'Invalid order ID'];
			}
			// Fetch order details
			$sql = "SELECT od.order_id, od.product_id, od.quantity, od.price, p.name AS product_name, p.image
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


}