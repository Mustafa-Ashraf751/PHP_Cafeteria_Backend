<?php

namespace App\Controllers;

use App\Services\ProductService;
use Exception;

class ProductController
{
    private $productService;

    public function __construct()
    {
        $this->productService = new ProductService();
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
            // Get JSON data from the request body
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            // Add validation for required fields
            if (!isset($data['name']) || !isset($data['price']) || !isset($data['category'])) {
                $this->jsonResponse([
                    'error' => 'Missing required fields',
                    'message' => 'Name, price and category are required.'
                ], 400);
            }

            // If quantity is not in JSON data, default to POST or set to 0
            $data['quantity'] = $data['quantity'] ?? ($_POST['quantity'] ?? 0);

            $result = $this->productService->addProduct($data);
            if ($result) {
                $this->jsonResponse(['message' => 'Product added successfully'], 201);
            } else {
                $this->jsonResponse(['error' => 'Failed to add product'], 400);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'error' => 'Failed to add product',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProduct()
    {
        try {
            $data = [
                'id' => $_POST['id'],
                'name' => $_POST['name'],
                'price' => $_POST['price'],
                'category' => $_POST['category'],
                'image_path' => $_FILES['image']['tmp_name'] ?? null,
                'image_url' => $_POST['image_url'] ?? null
            ];

            $result = $this->productService->updateProduct($data);
            if ($result) {
                $this->jsonResponse([
                    'message' => 'Updated Product',
                    'data' => $result
                ]);
            } else {
                $this->jsonResponse(['error' => 'Failed to update product'], 400);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'error' => 'Failed to update product',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteProduct($id)
    {
        try {
            $result = $this->productService->deleteProduct($id);
            if ($result) {
                $this->jsonResponse(['message' => 'Product deleted successfully'], 204);
            } else {
                $this->jsonResponse(['error' => 'Failed to delete product'], 400);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'error' => 'Failed to delete product',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}