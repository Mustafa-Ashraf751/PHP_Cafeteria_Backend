<?php

require_once __DIR__ . '/DataBase.php';
class UserModel
{
  private $db;
  private $tableName = 'User';

  public function __construct()
  {
    $this->db = Database::getDBConnection();
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
      $stmt->bindParam(':email', $email, PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      throw new Exception("Error fetch the user data by " . $email . $e->getMessage());
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
