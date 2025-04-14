<?php
namespace App\Services;
use App\Models\Room;

class RoomService {
  private $roomModel;

  public function __construct() {
    $this->roomModel = new Room();
  }

  public function getAllRooms() {
    return $this->roomModel->getAllRooms();
  }
}
