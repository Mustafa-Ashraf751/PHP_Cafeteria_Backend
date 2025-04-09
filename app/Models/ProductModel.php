<?php

namespace App\Models;

use PDO;
use PDOException;
use Exception;

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
    try {
      $query = "SELECT * FROM $this->tableName";

      $stmt = $this->db->prepare($query);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      throw new Exception("Error fetching products data: " . $e->getMessage());
    }
  }

  public function getProductById($id)
  {
    try {
      $query = "SELECT * FROM $this->tableName WHERE id = :id";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      throw new Exception("Error fetching the product data by ID " . $id . ": " . $e->getMessage());
    }
  }

  public function getProductByName($name)
  {
    try {
      $query = "SELECT * FROM $this->tableName WHERE name = :name";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':name', $name, PDO::PARAM_STR);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      throw new Exception("Error fetching the product data by name " . $name . ": " . $e->getMessage());
    }
  }

  public function createProduct($productData)
  {
    try {
      $fields = [];
      $placeholders = [];
      $values = [];

      foreach ($productData as $key => $value) {
        $fields[] = $key;
        $placeholders[] = ":$key";
        $values[":$key"] = $value;
      }

      $query = "INSERT INTO $this->tableName (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";

      $stmt = $this->db->prepare($query);
      $stmt->execute($values);

      return $this->db->lastInsertId();
    } catch (PDOException $e) {
      throw new Exception("Error creating product: " . $e->getMessage());
    }
  }

  public function updateProduct($id, $productData)
  {
    try {
      $fields = [];
      $params = [':id' => $id];

      foreach ($productData as $key => $value) {
        $fields[] = "$key = :$key";
        $params[":$key"] = $value;
      }

      $query = "UPDATE $this->tableName SET " . implode(', ', $fields) . " WHERE id = :id";
      $stmt = $this->db->prepare($query);
      return $stmt->execute($params);
    } catch (PDOException $e) {
      throw new Exception("Error updating product: " . $e->getMessage());
    }
  }

  public function deleteProduct($id)
  {
    try {
      $query = "DELETE FROM $this->tableName WHERE id = :id";

      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      return $stmt->execute();
    } catch (PDOException $e) {
      throw new Exception("Error deleting product: " . $e->getMessage());
    }
  }
}
