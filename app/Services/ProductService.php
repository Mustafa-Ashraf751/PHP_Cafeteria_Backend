<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
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

        // Configure Cloudinary
        $config = new Configuration();
        $config->cloud->cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'];
        $config->cloud->apiKey = $_ENV['CLOUDINARY_API_KEY'];
        $config->cloud->apiSecret = $_ENV['CLOUDINARY_API_SECRET'];

        $this->cloudinary = new Cloudinary($config);
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
        $productData = [
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'categoryId' => $data['categoryId'],
            'createdAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s')
        ];

        // Handle image upload if an image file is provided
        if (isset($data['image']) && !empty($data['image'])) {
            try {
                $uploadResult = $this->cloudinary->uploadApi()->upload($data['image'], [
                    'folder' => 'products',
                ]);
                $productData['image'] = $uploadResult['secure_url'];
            } catch (\Exception $e) {
                throw new \Exception('Image upload failed: ' . $e->getMessage());
            }
        } else {
            // Set a default image URL or leave it null based on your requirements
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
