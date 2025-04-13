<?php

namespace App\Models;


use PDO;
use PDOException;
use Dotenv\Dotenv;


require __DIR__ . '/../../vendor/autoload.php';

class DataBase
{
  private static $instance = null;
  private $conn;

  private function __construct()
  {
    $dotenv = Dotenv::createImmutable(__DIR__ . "/../../");
    $dotenv->load();
    try {
      $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_NAME'];
      $this->conn = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      die("Connection Failed!" . $e->getMessage());
    }
  }

  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new DataBase();
    }
    return self::$instance;
  }

  public function getConnection()
  {
    return $this->conn;
  }

  //Make static method to return $conn and use it anywhere
  public static function getDBConnection()
  {
    return self::getInstance()->getConnection();
  }
}
