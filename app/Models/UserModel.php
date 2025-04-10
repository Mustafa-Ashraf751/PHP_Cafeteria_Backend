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

  public function getAllUsers($page = 1, $perPage = 6)
  {
    try {
      //Eq to calc the offset
      $offset = ($page - 1) * $perPage;
      $query = "SELECT * FROM $this->tableName LIMIT :limit OFFSET :offset";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
      $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
      $stmt->execute();
      $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $countQuery = "SELECT COUNT(*) as total FROM $this->tableName";
      $countStmt = $this->db->prepare($countQuery);
      $countStmt->execute();
      $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
      return [
        'data' => $users,
        'pagination' => [
          'total' => (int)$totalCount,
          'per_page' => $perPage,
          'current_page' => $page,
          'last_page' => ceil($totalCount / $perPage),
          'from' => $offset + 1,
          'to' => min($offset + $perPage, $totalCount)
        ]
      ];
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

  public function createUser($user)
  {
    try {
      $stmt = $this->db->prepare("INSERT INTO users (fullName, email, password, roomNum, Ext, profilePic, role, roomId) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

      $stmt->execute([
        $user['fullName'],
        $user['email'],
        $user['password'], // Password should already be hashed in the service
        $user['roomNum'] ?? null,
        $user['Ext'] ?? null,
        $user['profilePic'] ?? null,
        $user['role'],
        $user['roomId'] ?? null
      ]);
      
      return $this->db->lastInsertId();
    } catch (PDOException $e) {
      throw new Exception("Error creating user: " . $e->getMessage());
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