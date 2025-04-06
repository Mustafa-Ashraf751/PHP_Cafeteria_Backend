<?php


namespace App\Controllers;

use App\Services\UserService;
use Exception;

class UserController
{

  private $userService;

  public function __construct()
  {
    $this->userService = new UserService();
  }

  //Create helper function to return json response
  private function jsonResponse($data, $statusCode = 200)
  {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
  }

  public function index()
  {
    try {
      $users = $this->userService->getAllUsers();
      $this->jsonResponse($users);
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }
}
