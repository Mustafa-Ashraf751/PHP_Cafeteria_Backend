<?php

namespace App\Controllers;

use App\Services\CategoryService;
use Exception;

class CategoryController
{
    private $categoryService;

    public function __construct()
    {
        $this->categoryService = new CategoryService();
    }

    protected function jsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit; // Critical to prevent additional output
    }
    public function addCategory()
    {
        try {
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate input
            if (empty($input['name'])) {
                $this->jsonResponse(['error' => 'Category name is required'], 400);
            }

            $newCategory = $this->categoryService->addCategory($input);

            if ($newCategory) {
                $this->jsonResponse([
                    'message' => 'Category created successfully',
                    'data' => $newCategory
                ], 201);  // 201 Created status
            } else {
                $this->jsonResponse(['error' => 'Failed to create category'], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'error' => 'Category creation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCategories()
    {
        try {
            $result = $this->categoryService->getCategoryList();
            if ($result) {
                $this->jsonResponse([
                    'message' => 'Categories retrieved',
                    'data' => $result
                ]);
            } else {
                $this->jsonResponse(['error' => 'Failed to retrieve categories'], 400);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'error' => 'Failed to retrieve categories',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteCategoryById($id)
    {
        try {
            // Validate ID format
            if (!is_numeric($id) || $id <= 0) {
                return $this->jsonResponse(['error' => 'Invalid category ID'], 400);
            }

            $success = $this->categoryService->deleteCategoryById($id);

            if ($success) {
                return $this->jsonResponse([
                    'message' => 'Category deleted successfully'
                ]);
            }

            return $this->jsonResponse(['error' => 'Category not found'], 404);
        } catch (Exception $e) {
            error_log("Controller delete error (ID: $id): " . $e->getMessage());
            return $this->jsonResponse([
                'error' => 'Failed to delete category',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function updateCategory($id)
    {
        try {
            // Get and validate input
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['name'])) {
                $this->jsonResponse(['error' => 'Category name is required'], 400);
            }

            // Update category
            $success = $this->categoryService->updateCategory($id, $input);

            if ($success) {
                // Get updated category to return in response
                $updatedCategory = $this->categoryService->getCategoryById($id);
                $this->jsonResponse([
                    'message' => 'Category updated successfully',
                    'data' => $updatedCategory
                ]);
            } else {
                $this->jsonResponse(['error' => 'Failed to update category'], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'error' => 'Update failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
