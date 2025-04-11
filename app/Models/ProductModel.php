<?php

namespace App\Models;

use App\Models\DataBase;
use PDO;
use PDOException;

class ProductModel
{
    private $db;
    private $tableName = 'products';

    public function __construct()
    {
        $this->db = DataBase::getDBConnection();
    }
    public function getAllProducts()
    {
        $query = "SELECT 
                    products.*, 
                    categories.name as category_name 
                  FROM products 
                  LEFT JOIN categories 
                    ON products.categoryId = categories.id";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getProductById($id)
    {
        $query = "SELECT * FROM $this->tableName WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function addProduct($data)
    {
        try {
            $query = "INSERT INTO $this->tableName 
                  (name, price, description, quantity, categoryId, image, createdAt, updatedAt) 
                  VALUES 
                  (:name, :price, :description, :quantity, :categoryId, :image, :createdAt, :updatedAt)";

            $stmt = $this->db->prepare($query);

            // Bind parameters with explicit types
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':price', $data['price'], PDO::PARAM_STR); // DECIMAL as string
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':quantity', $data['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':categoryId', $data['categoryId'], PDO::PARAM_INT);
            $stmt->bindParam(':image', $data['image'], PDO::PARAM_STR);
            $stmt->bindParam(':createdAt', $data['createdAt'], PDO::PARAM_STR);
            $stmt->bindParam(':updatedAt', $data['updatedAt'], PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Product insertion error: " . $e->getMessage());
            return false;
        }
    }

    public function updateProduct($data) {
        try {
            $query = "UPDATE $this->tableName 
            SET name = :name, price = :price, description = :description, 
                quantity = :quantity, categoryId = :categoryId, updatedAt = :updatedAt,
                status = CASE WHEN :quantity <= 0 THEN 'unavailable' ELSE 'available' END";
            
            if (isset($data['image'])) {
                $query .= ", image = :image";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':price', $data['price'], PDO::PARAM_STR); // DECIMAL as string
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':quantity', $data['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':categoryId', $data['categoryId'], PDO::PARAM_INT);
            $stmt->bindParam(':updatedAt', $data['updatedAt'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
            
            if (isset($data['image'])) {
                $stmt->bindParam(':image', $data['image'], PDO::PARAM_STR);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Product update error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteProduct($id)
    {
        $query = "DELETE FROM $this->tableName WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}