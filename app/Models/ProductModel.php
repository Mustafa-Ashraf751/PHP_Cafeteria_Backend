<?php

namespace App\Models;

use App\Models\DataBase;

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
        $query = "SELECT * FROM $this->tableName";
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
        $query = "INSERT INTO $this->tableName (name, price, description, quantity, categoryId, image, createdAt, updatedAt) 
                  VALUES (:name, :price, :description, :quantity, :categoryId, :image, :createdAt, :updatedAt)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':categoryId', $data['categoryId']);
        $stmt->bindParam(':image', $data['image']);
        $stmt->bindParam(':createdAt', $data['createdAt']);
        $stmt->bindParam(':updatedAt', $data['updatedAt']);

        return $stmt->execute();
    }

    public function updateProduct($data)
    {
        $query = "UPDATE $this->tableName 
                  SET name = :name, price = :price, description = :description, quantity = :quantity, 
                      categoryId = :categoryId, image = :image, updatedAt = :updatedAt 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':categoryId', $data['categoryId']);
        $stmt->bindParam(':image', $data['image']);
        $stmt->bindParam(':updatedAt', $data['updatedAt']);
        $stmt->bindParam(':id', $data['id']);

        return $stmt->execute();
    }

    public function deleteProduct($id)
    {
        $query = "DELETE FROM $this->tableName WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
