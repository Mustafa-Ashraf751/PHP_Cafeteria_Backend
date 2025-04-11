<?php

namespace App\Helpers\Query;

use App\Helpers\Query\QueryHelper;

class OrderQueryHelper
{
    public static function validateOrderSort(&$order_sort)
    {
        QueryHelper::validateOrderSort($order_sort);
    }

    public static function validateOrderField(&$order_field)
    {
        $allowedFields = ['id', 'order_status', 'total_amount', 'user_id', 'notes', 'room_id', 'created_at', 'updated_at'];
        QueryHelper::validateOrderField($order_field, $allowedFields, 'created_at');
    }
    public static function buildOrderPaginationMeta($totalCount, $page, $perPage, $offset)
    {
        return QueryHelper::buildPaginationMeta($totalCount, $page, $perPage, $offset);
    }
    public static function getOrderTableCount($db, $tableName)
    {
        return QueryHelper::getTableCount($db, $tableName);
    }
}
