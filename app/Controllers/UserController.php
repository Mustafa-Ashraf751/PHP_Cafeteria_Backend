<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Services\JwtService;
use Exception;

class UserController
{
  private $userService;
  private $jwtService;

  public function __construct()
  {
    $this->userService = new UserService();
    $this->jwtService = new JwtService();
  }

  private function jsonResponse($data, $statusCode = 200)
  {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
  }

  private function authenticateAdmin()
  {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
      $this->jsonResponse(['error' => 'Authorization header missing'], 401);
    }

    try {
      $token = str_replace('Bearer ', '', $headers['Authorization']);
      $decoded = $this->jwtService->verify($token);

      if (!isset($decoded->data->role) || $decoded->data->role !== 'admin') {
        $this->jsonResponse(['error' => 'Access denied. Admin privileges required'], 403);
      }

      return $decoded;
    } catch (Exception $e) {
      $this->jsonResponse(['error' => 'Invalid token: ' . $e->getMessage()], 401);
    }
  }

  public function login()
  {
    try {
      $data = json_decode(file_get_contents("php://input"), true);
      
      if (!isset($data['email']) || !isset($data['password'])) {
        $this->jsonResponse(['error' => 'Email and password are required'], 400);
      }

      $user = $this->userService->getUserByEmail($data['email']);

      if (!$user) {
          $this->jsonResponse(['error' => 'User not found'], 401);
      }

      if (password_verify($data['password'], $user['password']) === false) {
          $this->jsonResponse(['error' => 'Incorrect password'], 401);
      }else{
        $token = $this->jwtService->generate($user);
        $this->jsonResponse(['token' => $token, 'user' => $user]);
      }

      
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  public function register()
  {
    $this->authenticateAdmin();

    try {
      $userData = json_decode(file_get_contents('php://input'), true);
      if (!$userData) {
        $this->jsonResponse(['error' => 'Invalid input please try again'], 400);
      }
      
      $createdUserId = $this->userService->createUser($userData);
      $createdUser = $this->userService->getUserById($createdUserId);
      $this->jsonResponse(['message' => 'User registered successfully', 'user' => $createdUser], 201);
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  public function index()
  {
    $this->authenticateAdmin();
    
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
      // Check if user is requesting their own data or is an admin
      $headers = getallheaders();
      if (isset($headers['Authorization'])) {
        try {
          $token = str_replace('Bearer ', '', $headers['Authorization']);
          $decoded = $this->jwtService->verify($token);
          
          // If not admin and not requesting own profile, deny access
          if ($decoded->data->role !== 'admin' && $decoded->data->id != $userId) {
            $this->jsonResponse(['error' => 'Access denied'], 403);
          }
        } catch (Exception $e) {
          $this->jsonResponse(['error' => 'Invalid token: ' . $e->getMessage()], 401);
        }
      } else {
        $this->jsonResponse(['error' => 'Authorization required'], 401);
      }

      $user = $this->userService->getUserById($userId);
      if (!$user) {
        $this->jsonResponse(['error' => 'User not found please try again'], 404);
      }
      $this->jsonResponse($user);
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  public function update($id)
  {
    try {
      // Check if user is updating their own data or is an admin
      $headers = getallheaders();
      if (isset($headers['Authorization'])) {
        try {
          $token = str_replace('Bearer ', '', $headers['Authorization']);
          $decoded = $this->jwtService->verify($token);
          
          // If not admin and not updating own profile, deny access
          if ($decoded->data->role !== 'admin' && $decoded->data->id != $id) {
            $this->jsonResponse(['error' => 'Access denied'], 403);
          }
        } catch (Exception $e) {
          $this->jsonResponse(['error' => 'Invalid token: ' . $e->getMessage()], 401);
        }
      } else {
        $this->jsonResponse(['error' => 'Authorization required'], 401);
      }

      $userData = json_decode(file_get_contents('php://input'), true);
      if (!$userData) {
        $this->jsonResponse(['error' => 'Invalid input please try again'], 400);
      }
      
      // Prevent users from changing their own role, only admins can change roles
      if (isset($userData['role']) && $decoded->data->role !== 'admin') {
        unset($userData['role']);
      }
      
      $updated = $this->userService->updateUser($id, $userData);
      if ($updated) {
        $updatedUser = $this->userService->getUserById($id);
        $this->jsonResponse($updatedUser);
      } else {
        $this->jsonResponse(['error' => 'User not found please try again'], 404);
      }
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  public function delete($id)
  {
    $this->authenticateAdmin();
    
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