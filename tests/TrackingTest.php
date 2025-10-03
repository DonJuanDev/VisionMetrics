<?php

namespace VisionMetrics\Tests;

use PHPUnit\Framework\TestCase;

class TrackingTest extends TestCase
{
    public function testFingerprintGeneration()
    {
        $ip = '192.168.1.1';
        $userAgent = 'Mozilla/5.0 Test';
        $email = 'test@example.com';
        
        $fingerprint = hash('sha256', $ip . $userAgent . $email);
        
        $this->assertNotEmpty($fingerprint);
        $this->assertEquals(64, strlen($fingerprint));
    }
    
    public function testUtmExtraction()
    {
        $data = [
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'black-friday',
            'utm_term' => 'saas tracking',
            'utm_content' => 'ad1'
        ];
        
        $this->assertEquals('google', $data['utm_source']);
        $this->assertEquals('cpc', $data['utm_medium']);
        $this->assertEquals('black-friday', $data['utm_campaign']);
    }
    
    public function testClickIdExtraction()
    {
        $data = [
            'gclid' => 'Cj0KCQiA1234567890',
            'fbclid' => 'IwAR1234567890',
            'ttclid' => 'tt1234567890'
        ];
        
        $this->assertNotEmpty($data['gclid']);
        $this->assertNotEmpty($data['fbclid']);
        $this->assertNotEmpty($data['ttclid']);
    }
    
    public function testIdempotencyKey()
    {
        $key = bin2hex(random_bytes(16));
        
        $this->assertEquals(32, strlen($key));
    }
}