<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["endpoint"])) {
    $endpoint = $_GET["endpoint"];

    if ($endpoint === "products") {
        $products = file_get_contents("data/products.json");
        echo $products;
        exit;
    }
}

http_response_code(404);
echo json_encode(["error" => "Invalid endpoint"]);
?>
