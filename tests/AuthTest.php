<?php

namespace VisionMetrics\Tests;

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testPasswordHashing()
    {
        $password = 'ChangeMe123!';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('WrongPassword', $hash));
    }
    
    public function testApiKeyHashing()
    {
        $apiKey = 'vm_test_1234567890abcdef';
        $hash = hash('sha256', $apiKey);
        
        $this->assertNotEmpty($hash);
        $this->assertEquals(64, strlen($hash)); // SHA256 = 64 chars hex
    }
    
    public function testCsrfTokenGeneration()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        require_once __DIR__ . '/../src/csrf.php';
        
        $token1 = csrf_token();
        $token2 = csrf_token();
        
        $this->assertNotEmpty($token1);
        $this->assertEquals($token1, $token2); // Same session = same token
        $this->assertEquals(64, strlen($token1));
    }
    
    public function testSessionConfiguration()
    {
        $this->assertEquals('1', ini_get('session.cookie_httponly'));
        $this->assertEquals('1', ini_get('session.use_strict_mode'));
    }
}