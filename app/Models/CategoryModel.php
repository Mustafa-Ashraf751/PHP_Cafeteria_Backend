<?php

namespace App\Models;

use App\Models\DataBase;
use PDO;
use PDOException;

class CategoryModel
{
    private $db;
    private $tableName = 'categories';

    public function __construct()
    {
        $this->db = DataBase::getDBConnection();
    }
    public function getCategoryList()
    {
        $query = "SELECT * 
                  FROM {$this->tableName}";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getCategory($id)
    {
        try {
            $query = "SELECT * FROM {$this->tableName} WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get category error: " . $e->getMessage());
            return false;
        }
    }

    public function addCategory($data)
    {
        try {
            $query = "INSERT INTO {$this->tableName} (name) VALUES (:name)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
    
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            
            error_log("Category insertion failed without exception");
            return false;
            
        } catch (PDOException $e) {
            error_log("Database error in addCategory: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCategoryById($id)
{
    try {
        $query = "DELETE FROM {$this->tableName} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        
        return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
        error_log("Category deletion error (ID: $id): " . $e->getMessage());
        return false;
    }
}
    public function updateCategory($id, $data)
    {
        try {
            $query = "UPDATE {$this->tableName} 
                 SET name = :name 
                 WHERE id = :id";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Category update error: " . $e->getMessage());
            return false;
        }
    }
}
