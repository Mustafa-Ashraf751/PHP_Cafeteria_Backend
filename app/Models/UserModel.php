<?php

namespace App\Models;


use PDO;
use PDOException;
use Exception;

class UserModel
{
  private $db;
  private $tableName = 'users';

  public function __construct()
  {
    $this->db = DataBase::getDBConnection();
  }

  public function getAllUsers()
  {
    try {
      $query = "SELECT * FROM $this->tableName";

      $stmt = $this->db->prepare($query);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      throw new Exception("Error fetching users data" . $e->getMessage());
    }
  }


  public function getUserById($id)
  {
    try {
      $query = "SELECT * FROM $this->tableName WHERE id = :id";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      throw new Exception("Error fetch the user data by " . $id . $e->getMessage());
    }
  }

  public function getUserByEmail($email)
  {
    try {
      $query = "SELECT * FROM $this->tableName WHERE email = :email";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':email', $email, PDO::PARAM_STR);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      throw new Exception("Error fetch the user data by " . $email . $e->getMessage());
    }
  }

  public function createUser($userData)
  {
    try {
      $fields = [];
      //To protect database from the sql injection and attacks
      $placeholders = [];
      $values = [];

      //Using for loop to make the code more clean
      foreach ($userData as $key => $value) {
        $fields[] = $key;
        $placeholders[] = ":$key";
        $values[":$key"] = $value;
      }

      //Using implode function to don't write the fields hardcoded
      $query = "INSERT INTO $this->tableName (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";

      $stmt = $this->db->prepare($query);
      $stmt->execute($values);

      //return the id of the lastCreated to return it to the user in controller
      return $this->db->lastInsertId();
    } catch (PDOException $e) {
      throw new Exception("Error creating user please try again!");
    }
  }

  public function updateUser($id, $userData)
  {
    try {
      $fields = [];
      $params = [':id' => $id];

      foreach ($userData as $key => $value) {
        $fields[] = "$key = :$key";
        $params[":$key"] = $value;
      }

      $query = "UPDATE $this->tableName SET " . implode(', ', $fields) . " WHERE id = :id";
      $stmt = $this->db->prepare($query);
      return $stmt->execute($params);
    } catch (PDOException $e) {
      throw new Exception("Error updating user " . $e->getMessage());
    }
  }

  public function deleteUser($id)
  {
    try {
      $query = "DELETE FROM $this->tableName WHERE id = :id";

      $stmt = $this->db->prepare($query);

      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      return $stmt->execute();
    } catch (PDOException $e) {
      throw new Exception("Error deleting user: " . $e->getMessage());
    }
  }
}
