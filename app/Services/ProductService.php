<?php

namespace App\Services;

use App\Models\ProductModel;
use Exception;

class ProductService
{
  private $productModel;

  public function __construct()
  {
    $this->productModel = new ProductModel();
  }

  public function getAllProducts()
  {
    return $this->productModel->getAllProducts();
  }

  public function getProductById($id)
  {
    return $this->productModel->getProductById($id);
  }

  public function getProductByName($name)
  {
    return $this->productModel->getProductByName($name);
  }

  public function createProduct($productData)
  {
    // Validate the required fields
    if (empty($productData['name'])) {
      throw new Exception("Product name is required");
    }

    if (empty($productData['price']) || !is_numeric($productData['price']) || $productData['price'] <= 0) {
      throw new Exception("Valid product price is required");
    }

    if (empty($productData['category'])) {
      throw new Exception("Product category is required");
    }

    // Check if product with same name already exists
    $existingProduct = $this->productModel->getProductByName($productData['name']);
    if ($existingProduct) {
      throw new Exception("A product with this name already exists");
    }

    // Set default stock if not provided
    if (!isset($productData['stock_quantity'])) {
      $productData['stock_quantity'] = 0;
    }

    // Add timestamp
    $productData['created_at'] = date('Y-m-d H:i:s');

    return $this->productModel->createProduct($productData);
  }

  public function updateProduct($id, $productData)
  {
    // Check if product exists
    $product = $this->productModel->getProductById($id);
    if (!$product) {
      return false;
    }

    // If updating name, check for duplicate
    if (isset($productData['name']) && $productData['name'] !== $product['name']) {
      $existingProduct = $this->productModel->getProductByName($productData['name']);
      if ($existingProduct) {
        throw new Exception("A product with this name already exists");
      }
    }

    // Add updated timestamp
    $productData['updated_at'] = date('Y-m-d H:i:s');

    return $this->productModel->updateProduct($id, $productData);
  }

  public function deleteProduct($id)
  {
    return $this->productModel->deleteProduct($id);
  }
}
