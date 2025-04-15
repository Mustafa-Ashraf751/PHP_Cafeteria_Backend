<?php
namespace App\Controllers;
use App\Services\RoomService;

class RoomController
{
  private $roomService;

  public function __construct()
  {
    $this->roomService = new RoomService();
  }

  public function Show()
  {
    $rooms = $this->roomService->getAllRooms();
    //header('Content-Type: application/json');
    echo json_encode($rooms);
  }
}
