<?php
namespace App\Models;
//use App\Models\Database;
use PDO;
use PDOException;
use Exception;


class Room {
  private $db;

  public function __construct() {
          $this->db = DataBase::getDBConnection();
          $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }

  public function getAllRooms() {
    $stmt = $this->db->prepare("SELECT * FROM rooms");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
