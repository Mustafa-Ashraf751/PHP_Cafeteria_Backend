<?php

namespace App\Services;

use App\Models\CategoryModel;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class CategoryService
{
    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    public function addCategory($data)
    {
        try {
            // Validate before insertion
            if (empty($data['name'])) {
                throw new InvalidArgumentException('Category name is required');
            }
    
            $categoryData = [
                'name' => trim($data['name'])
            ];
    
            $newId = $this->categoryModel->addCategory($categoryData);
            
            if (!$newId) {
                throw new RuntimeException('Failed to create category in database');
            }
            
            // Return full created category
            return $this->getCategoryById($newId);
    
        } catch (Exception $e) {
            error_log("Service error in addCategory: " . $e->getMessage());
            return false;
        }
    }
    
    public function getCategoryList()
    {
        return $this->categoryModel->getCategoryList();
    }

    public function getCategoryById($id)
{
    try {
        $category = $this->categoryModel->getCategory($id);
        
        if (!$category) {
            throw new Exception("Category not found");
        }
        
        return $category;
        
    } catch (Exception $e) {
        error_log("Service error: " . $e->getMessage());
        return false;
    }
}

public function deleteCategoryById($id)
{
    try {
        // First verify category exists
        $category = $this->getCategoryById($id);
        if (!$category) {
            return false; // Not found
        }

        // Then attempt deletion
        return $this->categoryModel->deleteCategoryById($id);

    } catch (Exception $e) {
        error_log("Service delete error (ID: $id): " . $e->getMessage());
        return false;
    }
}
    public function updateCategory($id, $data)
    {
        $categoryData = [
            'name' => $data['name']
        ];
        return $this->categoryModel->updateCategory($id, $categoryData);
    }
}
