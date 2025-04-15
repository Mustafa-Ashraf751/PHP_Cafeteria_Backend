<?php

namespace App\Services;

use App\Models\UserModel;
use Exception;
use Cloudinary\Cloudinary;

class UserService
{


  private $userModel;
  private $cloudinary;

  public function __construct()
  {
    $this->userModel = new UserModel();
    $this->cloudinary = new Cloudinary([
      'cloud' => [
        'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
        'api_key'    => $_ENV['CLOUDINARY_API_KEY'],
        'api_secret' => $_ENV['CLOUDINARY_API_SECRET'],
      ]
    ]);
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

    if (!empty($userData['profilePic'])) {
      try {
        $uploadResult = $this->cloudinary->uploadApi()->upload($userData['profilePic'], [
          'folder' => 'profile_pics',
          'resource_type' => 'image'
        ]);
        $userData['profilePic'] = $uploadResult['secure_url'];
      } catch (\Exception $e) {
        throw new \RuntimeException('Profile picture upload failed: ' . $e->getMessage());
      }
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

    // Hash password if provided
    if (!empty($userData['password'])) {
      $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
    } else {
      // If password is not set or empty, remove it to avoid overwriting with empty value
      unset($userData['password']);
    }

    if (!empty($userData['profilePic'])) {
      try {
        $uploadResult = $this->cloudinary->uploadApi()->upload($userData['profilePic'], [
          'folder' => 'profile_pics',
          'resource_type' => 'image'
        ]);
        $userData['profilePic'] = $uploadResult['secure_url'];
      } catch (\Exception $e) {
        throw new \RuntimeException('Profile picture upload failed: ' . $e->getMessage());
      }
    }
    return $this->userModel->updateUser($id, $userData);
  }

  public function deleteUser($id)
  {
    return $this->userModel->deleteUser($id);
  }
}
