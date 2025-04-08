<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ProductTest extends TestCase
{
    private $http;
    private const BASE_URI = 'http://localhost/PHP_Cafeteria_Backend/public/api/products';

    protected function setUp(): void
    {
        $this->http = new Client([
            'base_uri' => self::BASE_URI,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function testGetAllProducts()
    {
        $response = $this->http->get('/');
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getBody(), true);
        $this->assertNotNull($data, 'Response should not be null');
        $this->assertIsArray($data, 'Response should be an array');
        
        if (!empty($data)) {
            $this->assertArrayHasKey('id', $data[0]);
            $this->assertArrayHasKey('name', $data[0]);
            $this->assertArrayHasKey('price', $data[0]);
        }
    }

    public function testCreateProduct()
    {
        $product = [
            'name' => 'Test Product',
            'price' => 9.99,
            'description' => 'Test description',
            'category_id' => 1
        ];

        $response = $this->http->post('/', [
            'json' => $product
        ]);
        
        $this->assertEquals(201, $response->getStatusCode());
        
        $data = json_decode($response->getBody(), true);
        $this->assertNotNull($data, 'Response should not be null');
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($product['name'], $data['name']);
        $this->assertEquals($product['price'], $data['price']);
    }

    public function testUpdateProduct()
    {
        $updateData = [
            'name' => 'Updated Product',
            'price' => 19.99,
            'description' => 'Updated description',
            'category_id' => 1
        ];

        $response = $this->http->put('/1', [
            'json' => $updateData
        ]);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getBody(), true);
        $this->assertNotNull($data, 'Response should not be null');
        $this->assertEquals($updateData['name'], $data['name']);
    }

    public function testDeleteProduct()
    {
        $response = $this->http->delete('/1');
        $this->assertEquals(204, $response->getStatusCode());
    }
}