<?php

namespace App\Models;

use PDO;
use PDOException;
use Exception;
use App\Helpers\Query\OrderQueryHelper;


class OrderModel
{
  private $db;
  private $tableName = 'orders';

  public function __construct()
  {
    $this->db = DataBase::getDBConnection();
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

}