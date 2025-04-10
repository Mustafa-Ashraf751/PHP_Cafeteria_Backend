<?php

namespace App\Services;

use App\Models\UserModel;
use Exception;

class UserService
{


  private $userModel;

  public function __construct()
  {
    $this->userModel = new UserModel();
  }

  public function getAllUsers($page = 1, $perPage = 10)
  {
    //Validate the parameters before send it to controller
    $page = max(1, (int)$page);
    $perPage = max(1, min(100, (int)$perPage));
    return $this->userModel->getAllUsers($page, $perPage);
  }

  public function getUserById($id)
  {
    return $this->userModel->getUserById($id);
  }

  public function getUserByEmail($email)
  {
    return $this->userModel->getUserByEmail($email);
  }

  public function createUser($userData)
  {
    //Validate the required fields here

    if (empty($userData['fullName'])) {
      throw new Exception("fullName is required please enter a role");
    }
    if (empty($userData['email']) || empty($userData['password'])) {
      throw new Exception("Email and password are required");
    }

    if (empty($userData['role'])) {
      throw new Exception("Role is required please enter a role");
    }

    //Check if email is already exist in database
    $existingUser = $this->userModel->getUserByEmail($userData['email']);
    if ($existingUser) {
      throw new Exception("Email is already exist!");
    }

    //Hashing password before save it to the database
    $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
    return $this->userModel->createUser($userData);
  }

  public function updateUser($id, $userData)
  {
    return $this->userModel->updateUser($id, $userData);
  }

  public function deleteUser($id)
  {
    return $this->userModel->deleteUser($id);
  }
}
