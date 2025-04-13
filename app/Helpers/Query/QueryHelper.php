<?php

namespace App\Helpers\Query;

use PDO;

class QueryHelper
{
    public static function validateOrderSort(&$orderSort)
    {
        $orderSort = strtoupper($orderSort);
        if (!in_array($orderSort, ['ASC', 'DESC'])) {
            $orderSort = 'ASC';
        }
    }

    public static function validateOrderField(&$orderField, array $allowedFields, $defaultOrderField = 'id')
    {
        if (!in_array($orderField, $allowedFields)) {
            $orderField = $defaultOrderField;
        }
    }
    public static function buildPaginationMeta($totalCount, $page, $perPage, $offset)
    {
        return [
            'total' => (int)$totalCount,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($totalCount / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $totalCount)
        ];
    }

    public static function getTableCount($db, $tableName)
    {
      $countQuery = "SELECT COUNT(*) as total FROM $tableName";
      $countStmt = $db->prepare($countQuery);
      $countStmt->execute();
      $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

			return $totalCount;
    }
}
