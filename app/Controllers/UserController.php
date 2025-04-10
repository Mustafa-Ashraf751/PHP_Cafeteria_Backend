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

  public function show($userId)
  {
    try {
      $user = $this->userService->getUserById($userId);
      if (!$user) {
        $this->jsonResponse(['error' => 'User not found please try again'], 404);
      }
      $this->jsonResponse($user);
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  public function store()
  {
    try {
      $userData = json_decode(file_get_contents('php://input'), true);
      if (!$userData) {
        $this->jsonResponse(['error' => 'Invalid input please try again'], 400);
      }
      $createdUserId = $this->userService->createUser($userData);
      $createdUser = $this->userService->getUserById($createdUserId);
      $this->jsonResponse($createdUser, 201);
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  public function update($id)
  {
    try {
      //Use php://input to access the raw body of the HTTP request then convert it to array
      $userData = json_decode(file_get_contents('php://input'), true);
      if (!$userData) {
        $this->jsonResponse(['error' => 'Invalid input please try again'], 400);
      }
      $updated = $this->userService->updateUser($id, $userData);
      if ($updated) {
        $updatedUser = $this->userService->getUserById($id);
      } else {
        $this->jsonResponse(['error' => 'User not found please try again'], 404);
      }
      $this->jsonResponse($updatedUser);
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage(), 500]);
    }
  }

  public function delete($id)
  {
    try {
      $deleted = $this->userService->deleteUser($id);
      if ($deleted) {
        $this->jsonResponse(['message' => 'User deleted successfully'], 204);
      } else {
        $this->jsonResponse(['error' => 'User not found please try again'], 404);
      }
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }
}
