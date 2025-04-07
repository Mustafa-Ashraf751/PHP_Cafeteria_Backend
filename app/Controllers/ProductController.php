<?php
require_once __DIR__ . '/../Models/productsModel.php';
require_once __DIR__ . '/../config/config.php'; 
use Cloudinary\Api\Upload\UploadApi;

class ProductController
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function handleRequest()
    {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];
        $requestData = json_decode(file_get_contents('php://input'), true);

        try {
            switch ($method) {
                case 'GET':
                    $products = $this->productModel->getAll();
                    echo json_encode($products);
                    break;

                case 'POST': 
                    $this->createProduct($requestData);
                    break;

                case 'PUT': 
                    $this->updateProduct($requestData);
                    break;

                case 'DELETE':

                    break;

                default:
                    throw new Exception('Invalid request method');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['message' => $e->getMessage()]);
        }
    }

    private function createProduct($data)
    {
        var_dump($data);
        exit;
        if (!isset($data['name'], $data['price'], $data['description'], $data['quantity'])) {
            throw new Exception('Missing required fields');
        }

        $imageUrl = null;
        if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
            $imageUrl = $this->uploadToCloudinary($_FILES['image']['tmp_name']);
        }

        $productId = $this->productModel->create([
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'categoryId' => $data['categoryId'] ?? null,
            'image' => $imageUrl,
        ]);

        echo json_encode(['message' => 'Product created successfully', 'id' => $productId]);
    }

    private function updateProduct($data)
    {
        if (!isset($data['id'])) {
            throw new Exception('Product ID is required for update');
        }

        $imageUrl = null;
        if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
            $imageUrl = $this->uploadToCloudinary($_FILES['image']['tmp_name']);
        }

        $updated = $this->productModel->update($data['id'], [
            'name' => $data['name'] ?? null,
            'price' => $data['price'] ?? null,
            'description' => $data['description'] ?? null,
            'quantity' => $data['quantity'] ?? null,
            'categoryId' => $data['categoryId'] ?? null,
            'image' => $imageUrl,
        ]);

        if ($updated) {
            echo json_encode(['message' => 'Product updated successfully']);
        } else {
            throw new Exception('Failed to update product');
        }
    }

    private function uploadToCloudinary($filePath)
    {
        $uploadApi = new UploadApi();
        $response = $uploadApi->upload($filePath);
        return $response['secure_url'] ?? null;
    }
}   