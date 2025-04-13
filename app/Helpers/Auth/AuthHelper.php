<?php

namespace App\Helpers\Auth;

use App\Services\JwtService;
use App\Helpers\Response\ResponseHelper;
use Exception;

class AuthHelper
{
    private $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function authenticateAdmin()
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            $this->sendUnauthorized('Authorization header missing');
        }

        try {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
            $decoded = $this->jwtService->verify($token);

            if (!isset($decoded->data->role)){
                $this->sendForbidden('Invalid token payload');
            }

            if ($decoded->data->role !== 'admin') {
                $this->sendForbidden('Admin privileges required');
            }

            return $decoded;
        } catch (Exception $e) {
            $this->sendUnauthorized('Invalid token: ' . $e->getMessage());
        }
    }

    public function authenticateUser($userId)
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            $this->sendUnauthorized('Authorization required');
        }

        try {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
            $decoded = $this->jwtService->verify($token);

            if ($decoded->data->role !== 'admin' && $decoded->data->id != $userId) {
                $this->sendForbidden('Access denied');
            }

            return $decoded;
        } catch (Exception $e) {
            $this->sendUnauthorized('Invalid token: ' . $e->getMessage());
        }
    }

    private function sendUnauthorized($message)
    {
        ResponseHelper::jsonResponse(['error' => $message], 401);
    }

    private function sendForbidden($message)
    {
        ResponseHelper::jsonResponse(['error' => $message], 403);
    }
}