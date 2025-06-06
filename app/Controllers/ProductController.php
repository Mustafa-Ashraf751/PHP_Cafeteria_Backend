<?php

namespace App\Controllers;

use App\Helpers\Auth\AuthHelper;
use App\Services\JwtService;
use App\Services\ProductService;
use Exception;

class ProductController
{
    private $productService;

    private $jwtService;
    private $authHelper;

    public function __construct()
    {
        $this->productService = new ProductService();
        $this->jwtService = new JwtService();
        $this->authHelper = new AuthHelper(new \App\Services\JwtService());
    }

    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function getAllProducts()
    {
        try {
            $products = $this->productService->getAllProducts();
            if ($products) {
                $this->jsonResponse($products);
            } else {
                $this->jsonResponse(['error' => 'Products not found'], 404);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'error' => 'Failed to fetch products',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getProductById($id)
    {
        try {
            $product = $this->productService->getProductById($id);
            if ($product) {
                $this->jsonResponse($product);
            } else {
                $this->jsonResponse(['error' => 'Product not found'], 404);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'error' => 'Failed to fetch product',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function addProduct()
    {
        try {

            $this->authHelper->authenticateAdmin();

            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            $isJson = strpos($contentType, 'application/json') !== false;
            $isMultipart = strpos($contentType, 'multipart/form-data') !== false;

            $input = [];

            if ($isJson) {
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->jsonResponse(['error' => 'Invalid JSON format'], 400);
                }
            } elseif ($isMultipart) {
                $input = $_POST;

                if (!empty($_FILES['image']['tmp_name'])) {
                    $input['image'] = $_FILES['image'];
                }
            } else {
                $this->jsonResponse(['error' => 'Unsupported content type'], 415);
            }

            $required = ['name', 'price', 'categoryId'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    $this->jsonResponse([
                        'error' => 'Missing required field: ' . $field
                    ], 400);
                }
            }

            if (!is_numeric($input['price']) || $input['price'] <= 0) {
                $this->jsonResponse(['error' => 'Invalid price format'], 400);
            }

            if (isset($input['image'])) {
                if (is_array($input['image'])) {
                    $input['image'] = $input['image']['tmp_name'];
                } elseif ($isJson && preg_match('/^data:image\/(\w+);base64,/', $input['image'], $matches)) {
                    $input['image'] = $this->handleBase64Image($input['image']);
                }
            }

            $result = $this->productService->addProduct($input);

            if ($result) {
                $this->jsonResponse([
                    'message' => 'Product created successfully',
                    'data' => $result
                ], 201);
            }

            $this->jsonResponse(['error' => 'Product creation failed'], 500);
        } catch (Exception $e) {
            $this->jsonResponse([
                'error' => 'Product creation error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function handleBase64Image($base64Image)
    {
        try {
            list($type, $data) = explode(';', $base64Image);
            list(, $data) = explode(',', $data);
            $data = base64_decode($data);
            $extension = explode('/', $type)[1];

            $tempPath = tempnam(sys_get_temp_dir(), 'img') . '.' . $extension;
            file_put_contents($tempPath, $data);

            return $tempPath;
        } catch (Exception $e) {
            throw new Exception('Invalid image format: ' . $e->getMessage());
        }
    }

    public function updateProduct($id)
    {
        try {
            $this->authHelper->authenticateAdmin();

            if (isset($_POST['category']) && !isset($_POST['categoryId'])) {
                $_POST['categoryId'] = $_POST['category'];
            }
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            $isJson = strpos($contentType, 'application/json') !== false;
            $isMultipart = strpos($contentType, 'multipart/form-data') !== false;

            $input = [];

            if ($isJson) {
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->jsonResponse(['error' => 'Invalid JSON format'], 400);
                }
            } elseif ($isMultipart) {
                $input = $_POST;

                if (!empty($_FILES['image']['tmp_name'])) {
                    $input['image'] = $_FILES['image']['tmp_name'];
                }
            } else {
                $this->jsonResponse(['error' => 'Unsupported content type'], 415);
            }

            if (isset($input['category']) && !isset($input['categoryId'])) {
                $input['categoryId'] = $input['category'];
            }

            $required = ['name', 'price'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    $this->jsonResponse([
                        'error' => 'Missing required field: ' . $field
                    ], 400);
                }
            }

            if (empty($input['categoryId'])) {
                $this->jsonResponse([
                    'error' => 'Missing required field: categoryId'
                ], 400);
            }

            if (!is_numeric($input['price']) || $input['price'] <= 0) {
                $this->jsonResponse(['error' => 'Invalid price format'], 400);
            }

            if (isset($input['image']) && $isJson && !is_string($input['image']['tmp_name'] ?? null)) {
                if (preg_match('/^data:image\/(\w+);base64,/', $input['image'], $matches)) {
                    $input['image'] = $this->handleBase64Image($input['image']);
                }
            }

            $data = [
                'id' => $id,
                'name' => $input['name'],
                'price' => (float) $input['price'],
                'categoryId' => (int) $input['categoryId'],
                'description' => $input['description'] ?? '',
                'quantity' => (int) ($input['quantity'] ?? 0),
                'updatedAt' => date('Y-m-d H:i:s')
            ];

            if (isset($input['image'])) {
                $data['image'] = $input['image'];
            }

            $result = $this->productService->updateProduct($data);

            if ($result) {
                $this->jsonResponse([
                    'message' => 'Product updated successfully',
                    'data' => $result
                ]);
            } else {
                $this->jsonResponse(['error' => 'Failed to update product'], 400);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'error' => 'Update failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteProduct($id)
    {
        try {
            $this->authHelper->authenticateAdmin();

            $result = $this->productService->deleteProduct($id);
            if ($result) {
                return $this->jsonResponse([], 204);
            } else {
                return $this->jsonResponse(['error' => 'Product not found'], 404);
            }
        } catch (Exception $e) {
            return $this->jsonResponse([
                'error' => 'Failed to delete product',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}