<?php

namespace App\Services;

use App\Models\OrderModel;
use Exception;

class OrderService
{
  private $orderModel;

  public function __construct()
  {
    $this->orderModel = new OrderModel();
  }

  public function getAllOrders($page = 1, $perPage = 10, $orderField= "created_at", $orderSort= "ASC")
  {
    //Validate the parameters before send it to controller
    $page = max(1, (int)$page);
    $perPage = max(1, min(100, (int)$perPage));
    return $this->orderModel->getAllOrders($page, $perPage, $orderField, $orderSort);
  }

}
