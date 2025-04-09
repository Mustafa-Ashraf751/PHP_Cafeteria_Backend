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

  public function index()
  {
    try {
      $products = $this->productService->getAllProducts();
      $this->jsonResponse($products);
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  public function show($productId)
  {
    try {
      $product = $this->productService->getProductById($productId);
      if (!$product) {
        $this->jsonResponse(['error' => 'Product not found'], 404);
      }
      $this->jsonResponse($product);
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  public function store()
  {
    try {
      $productData = json_decode(file_get_contents('php://input'), true);
      if (!$productData) {
        $this->jsonResponse(['error' => 'Invalid input data'], 400);
      }
      $createdProductId = $this->productService->createProduct($productData);
      $createdProduct = $this->productService->getProductById($createdProductId);
      $this->jsonResponse($createdProduct, 201);
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  public function update($id)
  {
    try {
      $productData = json_decode(file_get_contents('php://input'), true);
      if (!$productData) {
        $this->jsonResponse(['error' => 'Invalid input data'], 400);
      }
      $updated = $this->productService->updateProduct($id, $productData);
      if ($updated) {
        $updatedProduct = $this->productService->getProductById($id);
        $this->jsonResponse($updatedProduct);
      } else {
        $this->jsonResponse(['error' => 'Product not found'], 404);
      }
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }

  public function delete($id)
  {
    try {
      $deleted = $this->productService->deleteProduct($id);
      if ($deleted) {
        $this->jsonResponse(['message' => 'Product deleted successfully'], 204);
      } else {
        $this->jsonResponse(['error' => 'Product not found'], 404);
      }
    } catch (Exception $e) {
      $this->jsonResponse(['error' => $e->getMessage()], 500);
    }
  }
}
