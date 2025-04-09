<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Dotenv\Dotenv;
use App\Models\ProductModel;

class ProductService
{
    private $productModel;
    private $cloudinary;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->productModel = new ProductModel();

        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'],
                'api_key'    => $_ENV['CLOUDINARY_API_KEY'],
                'api_secret' => $_ENV['CLOUDINARY_API_SECRET'],
            ]
        ]);
    }

    public function getAllProducts()
    {
        return $this->productModel->getAllProducts();
    }

    public function getProductById($id)
    {
        return $this->productModel->getProductById($id);
    }

    public function addProduct($data)
    {
        // Validate required fields
        if (empty($data['categoryId'])) {
            throw new \InvalidArgumentException('Category ID is required');
        }
    
        $productData = [
            'name' => $data['name'],
            'price' => number_format((float)$data['price'], 2),
            'description' => $data['description'] ?? '',
            'quantity' => (int)($data['quantity'] ?? 0),
            'categoryId' => (int)$data['categoryId'],
            'createdAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s')
        ];
    
        // Handle image upload
        if (!empty($data['image'])) {
            try {
                $uploadResult = $this->cloudinary->uploadApi()->upload($data['image'], [
                    'folder' => 'products',
                    'resource_type' => 'image'
                ]);
                $productData['image'] = $uploadResult['secure_url'];
            } catch (\Exception $e) {
                throw new \RuntimeException('Image upload failed: ' . $e->getMessage());
            }
        } else {
            $productData['image'] = null;
        }
    
        return $this->productModel->addProduct($productData);
    }

    public function updateProduct($data)
    {
        $productData = [
            'id' => $data['id'],
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'categoryId' => $data['categoryId'],
            'updatedAt' => date('Y-m-d H:i:s')
        ];

        if (!empty($data['image_path'])) {
            $uploadResult = $this->cloudinary->uploadApi()->upload($data['image_path'], [
                'folder' => 'products',
            ]);
            $productData['image'] = $uploadResult['secure_url'];
        } else {
            $productData['image'] = $data['image'];
        }

        return $this->productModel->updateProduct($productData);
    }

    public function deleteProduct($id)
    {
        return $this->productModel->deleteProduct($id);
    }
    
}
