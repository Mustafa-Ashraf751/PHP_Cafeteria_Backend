<?php

namespace App\Services;

use App\Models\UserModel;

class UserService
{


  private $userModel;

  public function __construct()
  {
    $this->userModel = new UserModel();
  }

  public function getAllUsers()
  {
    return $this->userModel->getAllUsers();
  }

  public function getUserById($id)
  {
    return $this->userModel->getUserById($id);
  }

  public function getUserByEmail($email)
  {
    return $this->userModel->getUserByEmail($email);
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
