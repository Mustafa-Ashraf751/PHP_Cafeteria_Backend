<?php

namespace App\Services;

use App\Helpers\Response\ResponseHelper;
use App\Models\OrderModel;
use Exception;
use SebastianBergmann\Environment\Console;

class OrderService
{
	private $orderModel;

	public function __construct()
	{
		$this->orderModel = new OrderModel();
	}

	// create order
	public function createOrder($userId, $roomId, $totalAmount, $notes, $orderItems)
	{
		try {
			if (empty($userId) || empty($roomId) || empty($totalAmount)) {
				return ['status' => 'error', 'message' => 'Invalid order data.'];
			}

			// add order by use Model Order
			$orderId = $this->orderModel->createOrder($userId, $roomId, $totalAmount, $notes, $orderItems);

			if ($orderId) {
				return ['status' => 'success', 'message' => 'Order created successfully from if().', 'orderId' => $orderId];
			} else {
				return ['status' => 'error', 'message' => 'Failed to create order in database.'];
			}
		} catch (Exception $e) {
			error_log("Order Items received in ServiceOrder: " . print_r($orderItems, true));

			return ['status' => 'error', 'message' => 'Failed to create order: ' . $e->getMessage()];
		}
	}

	// get all orders
	public function getAllOrders($page = 1, $perPage = 10, $orderField = "created_at", $orderSort = "ASC")
	{
		try {
			//Validate the parameters before send it to controller
			$page = max(1, (int) $page);
			$perPage = max(1, min(100, (int) $perPage));
			return $this->orderModel->getAllOrders($page, $perPage, $orderField, $orderSort);
		} catch (Exception $e) {
			return ['status' => 'error', 'message' => 'Failed to fetch orders: ' . $e->getMessage()];
		}
	}

	// get order by id
	public function getOrderById($id)
	{
		try {
			$order = $this->orderModel->getOrderById($id);

			// Check if the order exists
			if (isset($order['status']) && $order['status'] === 'error') {
				return null;
			}

			return $order;
		} catch (Exception $e) {
			return ['status' => 'error', 'message' => 'Failed to fetch order'];
		}
	}

	public function getOrderDetails($orderId)
	{
		try {

			if (empty($orderId) || !is_numeric($orderId) || $orderId <= 0) {
				return ['status' => 'error', 'message' => 'Invalid order ID'];
			}

			$orderDetails = $this->orderModel->getOrderDetails($orderId);

			if (!$orderDetails || isset($orderDetails['status']) && $orderDetails['status'] === 'error') {
				return ['status' => 'error', 'message' => 'Order details not found'];
			}

			return ['status' => 'success', 'orderDetails' => $orderDetails];
		} catch (Exception $e) {
			return ['status' => 'error', 'message' => 'Failed to fetch order details: ' . $e->getMessage()];
		}
	}

	// update order status
	public function updateOrderStatus($id, $orderStatus)
	{
		try {
			$order = $this->orderModel->getOrderById($id);
			if (!$order) {
				return ['status' => 'error', 'message' => 'Order not found.'];
			}

			$this->orderModel->updateOrderStatus($id, $orderStatus);
			return ['status' => 'success', 'message' => 'Order status updated successfully.'];
		} catch (Exception $e) {
			return ['status' => 'error', 'message' => 'Failed to update order status: ' . $e->getMessage()];
		}
	}

	// cancel order (replaces delete)
	public function cancelOrder($id)
	{
		try {
			$order = $this->orderModel->getOrderById($id);
			if (!$order) {
				return ['status' => 'error', 'message' => 'Order not found.'];
			}

			$this->orderModel->cancelOrder($id);
			return ['status' => 'success', 'message' => 'Order cancelled successfully.'];
		} catch (Exception $e) {
			return ['status' => 'error', 'message' => 'Failed to cancel order: ' . $e->getMessage()];
		}
	}

	public function getOrderByUserAndDate($userId, $startDate = null, $endDate = null)
	{
		try {
			if (empty($userId)) {
				return [
					'status' => 'error',
					'message' => 'User ID is required'
				];
			}

			if ($startDate && !strtotime($startDate)) {
				return [
					'status' => 'error',
					'message' => 'Invalid start time'
				];
			}

			if ($endDate && !strtotime($endDate)) {
				return [
					'status' => 'error',
					'message' => 'Invalid end time'
				];
			}

			// Get orders from model
			$result = $this->orderModel->getOrdersByUserAndDateRange($userId, $startDate, $endDate);

			if (empty($result['orders'])) {
				return [
					'status' => 'success',
					'data' => [],
					'summary' => [
						'total_orders' => 0,
						'total_amount' => 0
					],
					'message' => 'No orders found with this user try again'
				];
			}

			return [
				'status' => 'success',
				'data' => $result['orders'],
				'summary' => [
					'total_orders' => $result['count'],
					'total_amount' => $result['total_amount']
				],
			];
		} catch (Exception $e) {
			return [
				'status' => 'error',
				'message' => 'Failed to fetch orders: ' . $e->getMessage()
			];
		}
	}



	public function getAllUsersWithOrderSummary($page = 1, $perPage = 10, $userId = null, $startDate = null, $endDate = null)
	{
		try {
			// Validate pagination parameters
			$page = max(1, (int) $page);
			$perPage = max(1, min(100, (int) $perPage));

			// Validate date formats if provided
			if ($startDate && !strtotime($startDate)) {
				return [
					'status' => 'error',
					'message' => 'Invalid start date format. Use YYYY-MM-DD'
				];
			}

			if ($endDate && !strtotime($endDate)) {
				return [
					'status' => 'error',
					'message' => 'Invalid end date format. Use YYYY-MM-DD'
				];
			}

			if ($userId !== null && (!is_numeric($userId) || (int) $userId <= 0)) {
				return [
					'status' => 'error',
					'message' => 'Invalid user id please provide valid user id'
				];
			}

			// Get paginated data from model
			$result = $this->orderModel->getAllUsersWithOrderSummary($page, $perPage, $userId, $startDate, $endDate);

			if (empty($result['data'])) {
				return [
					'status' => 'success',
					'data' => [],
					'message' => 'No users with orders found',
					'pagination' => [
						'page' => $page,
						'per_page' => $perPage,
						'total_items' => 0,
						'total_pages' => 0
					]
				];
			}

			return [
				'status' => 'success',
				'data' => $result['data'],
				'pagination' => $result['pagination'],
				'filters' => [
					'start_date' => $startDate,
					'end_date' => $endDate
				]
			];
		} catch (Exception $e) {
			return [
				'status' => 'error',
				'message' => 'Failed to fetch users with order summary: ' . $e->getMessage()
			];
		}
	}

	public function getOrderInfo($order_id)
	{
		try {
			if (empty($order_id)) {
				return ['status' => 'error', 'message' => 'Invalid order ID.'];
			}
			$result = $this->orderModel->getOrderInfo($order_id);


			return ['status' => 'success', 'data' => $result];
		} catch (Exception $e) {
			return ['status' => 'error', 'message' => 'Failed to fetch order info: ' . $e->getMessage()];
		}
	}

	public function getUserOfOrder($order_id)
	{
		try {
			if (empty($order_id)) {
				ResponseHelper::jsonResponse(['status' => 'error', 'message' => 'Invalid order ID.'], 400);
			}

			$result = $this->orderModel->getUserOfOrder($order_id);
			return $result;
		} catch (Exception $e) {
			return ['status' => 'error', 'message' => 'Failed to fetch order info: ' . $e->getMessage()];
		}
	}

}
