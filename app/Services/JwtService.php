<?php

namespace App\Services;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private $secretKey;
    private $algorithm;

    public function __construct()
    {
        // Get the secret key from environment variables
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'default_secret_key_change_in_production';
        $this->algorithm = 'HS256';
    }

    public function generate($userData)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600 * 24;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => [
                'id' => $userData['id'],
                'email' => $userData['email'],
                'role' => $userData['role']
            ]
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    public function verify($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return $decoded;
        } catch (Exception $e) {
            throw new Exception('Invalid token: ' . $e->getMessage());
        }
    }
}
