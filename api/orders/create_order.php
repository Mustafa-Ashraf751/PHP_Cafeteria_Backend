<?php
require_once '../../config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$rawData = file_get_contents("php://input");
var_dump($rawData);
$data = json_decode($rawData, true);

header('Content-Type: application/json');
echo json_encode(["received_data" => $data]);
exit;


// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $data = json_decode(file_get_contents("php://input"), true);

//     if (!isset($data['user_id'], $data['product_id'], $data['quantity'], $data['total_price'])) {
//         echo json_encode(["error" => "all filed are required"]);
//         http_response_code(400);
//         exit;
//     }

//     $user_id = $data['user_id'];
//     $product_id = $data['product_id'];
//     $quantity = $data['quantity'];
//     $total_price = $data['total_price'];
//     $notes = isset($data['notes']) ? $data['notes'] : null;

//     try {
//         $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, notes, total_price) 
//                                 VALUES (:user_id, :product_id, :quantity, :notes, :total_price)");
//         $stmt->bindParam(':user_id', $user_id);
//         $stmt->bindParam(':product_id', $product_id);
//         $stmt->bindParam(':quantity', $quantity);
//         $stmt->bindParam(':notes', $notes);
//         $stmt->bindParam(':total_price', $total_price);

//         if ($stmt->execute()) {
//             echo json_encode(["message" => "request created successfully"]);
//         } else {
//             echo json_encode(["error" => "request failed"]);
//             http_response_code(500);
//         }
//     } catch (PDOException $e) {
//         echo json_encode(["error" => $e->getMessage()]);
//         http_response_code(500);
//     }
// }
?>
