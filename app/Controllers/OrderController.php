<?php

namespace App\Controllers;

use App\Services\OrderService;
use App\Helpers\Auth\AuthHelper;
use App\Helpers\Response\ResponseHelper;
use Exception;

class OrderController
{
    private $orderService;
    private $authHelper;

    public function __construct()
    {
        $this->orderService = new OrderService();
        $this->authHelper = new AuthHelper(new \App\Services\JwtService());
    }

    public function index()
    {
        $this->authHelper->authenticateAdmin();

        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 6;
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
}