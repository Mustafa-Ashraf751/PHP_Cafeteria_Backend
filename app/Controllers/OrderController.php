<?php

namespace App\Controllers;

use App\Services\OrderService;
use Exception;
use App\Services\JwtService;
use App\Helpers\Auth\AuthHelper;
use App\Helpers\Response\ResponseHelper;

class OrderController
{
	private $orderService;
	private $jwtService;
	private $authHelper;

	public function __construct()
	{
		$this->orderService = new OrderService();
		$this->jwtService = new JwtService();
		$this->authHelper = new AuthHelper(new \App\Services\JwtService());
	}

	// method to create a new order
	public function store()
	{

		// $this->authHelper->authenticateUser($user['user_id']);

		file_put_contents(__DIR__ . '/../debug_log.txt', "store() called\n", FILE_APPEND);

		// get data from request body
		$data = json_decode(file_get_contents('php://input'), true);
		// check if data is valid
		file_put_contents(__DIR__ . '/../debug_log.txt', print_r($data, true), FILE_APPEND);
		error_log('' . json_encode($data, true));
		if (!isset($data['user_id'], $data['room_id'], $data['total_amount'], $data['order_items'])) {
			ResponseHelper::jsonResponse(['status' => 'error', 'message' => 'Missing required fields'], 400);
			return;
		}

		$orderItems = isset($data['order_items']) ? $data['order_items'] : [];
		if (empty($orderItems)) {
			ResponseHelper::jsonResponse(['status' => 'error', 'message' => 'Order items are required'], 400);
			return;
		}
		// extract individual values
		$userId = $data['user_id'];
		$roomId = $data['room_id'];  // Changed from 'room' to 'room_id'
		$totalAmount = $data['total_amount'];  // Changed from 'total_price' to 'total_amount'
		$notes = $data['notes'] ?? null;  // Changed from 'note' to 'notes'
		$orderItems = isset($data['order_items']) ? $data['order_items'] : [];
		// connect to the service to create the order


		$response = $this->orderService->createOrder($userId, $roomId, $totalAmount, $notes, $orderItems);

		if ($response['status'] === 'success') {

			ResponseHelper::jsonResponse(['status' => 'success', 'message' => 'Order created successfully.', 'orderId' => $response['orderId']]);
		} else {
			ResponseHelper::jsonResponse($response);
		}
	}
	// Index route with pagination and sorting
	public function index()
	{
		$this->authHelper->authenticateAdmin();

		try {
			$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
			$perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 6;
			$orderField = $_GET['order_field'] ?? 'created_at';
			$orderSort = $_GET['order_sort'] ?? 'ASC';

			$orders = $this->orderService->getAllOrders(
				$page,
				$perPage,
				$orderField,
				$orderSort
			);

			ResponseHelper::jsonResponse($orders);
		} catch (Exception $e) {
			ResponseHelper::jsonResponse(['error' => $e->getMessage()], 500);
		}
	}

	// method to show a single order
	public function show($id)
	{
		if (empty($id) || !is_numeric($id)) {
			ResponseHelper::jsonResponse(['status' => 'error', 'message' => 'Invalid order ID.'], 400);
		}

		$user = $this->orderService->getUserOfOrder($id);

		$this->authHelper->authenticateUser($user['user_id']);

		if (!is_numeric($id) || $id <= 0) {
			http_response_code(400);
			echo json_encode(["error" => "Invalid order ID format"]);
			return;
		}

		$order = $this->orderService->getOrderById((int) $id);

		if (!$order || (isset($order['status']) && $order['status'] === 'error')) {
			http_response_code(404);
			echo json_encode(["error" => "Order with ID $id not found"]);
			return;
		}

		http_response_code(200);
		echo json_encode($order);
	}

	// method to update order status
	public function updateStatus($id)
	{
		// $orderId = $params['id'];
		// $status = $params['status'];
		$data = json_decode(file_get_contents('php://input'), true);

		$order = $this->orderService->getOrderById($id);
		if (!isset($data['status'])) {
			http_response_code(400);
			echo json_encode(["error" => "Missing status parameter"]);
			return;
		}

		if (!$order) {
			echo json_encode(["status" => "error", "message" => "Order not found"]);
			return;
		}
		$response = $this->orderService->updateOrderStatus($id, $data['status']);

		echo json_encode($response);
	}

	public function getOrderDetails($orderId)
	{
		if (!is_numeric($orderId) || $orderId <= 0) {
			http_response_code(400);
			echo json_encode(["error" => "Invalid order ID format"]);
			return;
		}


		$result = $this->orderService->getOrderDetails($orderId);


		if (isset($result['status']) && $result['status'] === 'error') {
			http_response_code(404);
			echo json_encode(["error" => "Order details not found"]);
		} else {
			http_response_code(200);
			echo json_encode($result);
		}
	}

	// method to cancel an order (replaces delete)
	public function cancel($id)
	{
		if (empty($id) || !is_numeric($id)) {
			ResponseHelper::jsonResponse(['status' => 'error', 'message' => 'Invalid order ID.'], 400);
		}

		$user = $this->orderService->getUserOfOrder($id);

		$this->authHelper->authenticateUser($user['user_id']);

		$data = json_decode(file_get_contents('php://input'), true);
		// $orderId = $params['id'];

		$order = $this->orderService->getOrderById($id);
		if (!isset($data['status'])) {
			http_response_code(400);
			echo json_encode(["error" => "Missing status parameter"]);
			return;
		}
		if (!$order) {
			echo json_encode(["status" => "error", "message" => "Order not found"]);
			return;
		}

		// connect to the service to cancel the order
		$response = $this->orderService->cancelOrder($id);

		// send the response
		echo json_encode($response);
	}

	public function getUserOrders($userId)
	{
		$this->authHelper->authenticateUser($userId);

		$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
		$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

		$response = $this->orderService->getOrderByUserAndDate($userId, $startDate, $endDate);
		$statusCode = $response['status'] === 'success' ? 200 : 500;
		http_response_code($statusCode);

		// Send response
		echo json_encode($response);
	}

	public function getUsersWithOrders($page = 1, $perPage = 10, $orderField = "created_at", $orderSort = "ASC")
	{
		try {
			$this->authHelper->authenticateAdmin();
			$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
			$perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 6;
			$orderField = $_GET['order_field'] ?? 'created_at';
			$orderSort = $_GET['order_sort'] ?? 'ASC';

			$orders = $this->orderService->getUsersWithOrders(
				$page,
				$perPage,
				$orderField,
				$orderSort
			);

			ResponseHelper::jsonResponse($orders);
		} catch (Exception $e) {
			ResponseHelper::jsonResponse(['error' => $e->getMessage()], 500);
		}
	}

	public function getAllUsersWithOrderSummary()
	{
		// Only allow admins to access this endpoint
		$this->authHelper->authenticateAdmin();

		// Get pagination parameters
		$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
		$perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;

		// Get date filters
		$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : null;
		$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
		$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

		// Get users with orders summary
		$response = $this->orderService->getAllUsersWithOrderSummary($page, $perPage, $userId, $startDate, $endDate);

		// Send response
		ResponseHelper::jsonResponse($response);
	}

	public function getOrderInfo($order_id)
	{

		try {
			if (empty($order_id) || !is_numeric($order_id)) {
				ResponseHelper::jsonResponse(['status' => 'error', 'message' => 'Invalid order ID.'], 400);
			}

			$user = $this->orderService->getUserOfOrder($order_id);

			$this->authHelper->authenticateUser($user['user_id']);
			error_log('' . $order_id);
			$orders = $this->orderService->getOrderInfo(
				$order_id
			);

			if ($orders['status'] === 'error') {
				ResponseHelper::jsonResponse(['error' => $orders['message']], 500);
			}

			ResponseHelper::jsonResponse($orders);
		} catch (Exception $e) {
			ResponseHelper::jsonResponse(['error' => $e->getMessage()], 500);
		}
	}
}
