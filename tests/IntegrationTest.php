<?php

namespace VisionMetrics\Tests;

use PHPUnit\Framework\TestCase;
use VisionMetrics\Adapters\MetaAdapter;
use VisionMetrics\Adapters\GA4Adapter;
use VisionMetrics\Adapters\WhatsAppAdapter;

class IntegrationTest extends TestCase
{
    public function testMetaAdapterSimulateMode()
    {
        $_ENV['ADAPTER_MODE'] = 'simulate';
        $_ENV['META_PIXEL_ID'] = '123456';
        $_ENV['META_ACCESS_TOKEN'] = 'test';
        
        $adapter = new MetaAdapter();
        $result = $adapter->sendConversion('Lead', [
            'email' => 'test@example.com',
            'phone' => '+5511999999999'
        ]);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('simulated', $result['mode']);
    }
    
    public function testGA4AdapterSimulateMode()
    {
        $_ENV['ADAPTER_MODE'] = 'simulate';
        $_ENV['GA4_MEASUREMENT_ID'] = 'G-TEST123';
        $_ENV['GA4_API_SECRET'] = 'test-secret';
        
        $adapter = new GA4Adapter();
        $result = $adapter->sendEvent('page_view', 'client.123', [
            'page_location' => 'https://example.com'
        ]);
        
        $this->assertTrue($result['success']);
    }
    
    public function testWhatsAppAdapterSimulateMode()
    {
        $_ENV['ADAPTER_MODE'] = 'simulate';
        $_ENV['WHATSAPP_PHONE_ID'] = '123456';
        $_ENV['WHATSAPP_ACCESS_TOKEN'] = 'test';
        
        $adapter = new WhatsAppAdapter();
        $result = $adapter->sendMessage('+5511999999999', 'Test message');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message_id', $result);
    }
    
    public function testUserDataHashing()
    {
        $email = 'test@example.com';
        $hashed = hash('sha256', strtolower(trim($email)));
        
        $this->assertEquals(64, strlen($hashed));
        $this->assertEquals(
            hash('sha256', 'test@example.com'),
            $hashed
        );
    }
    
    public function testPhoneNormalization()
    {
        $phone = '+55 (11) 99999-9999';
        $normalized = preg_replace('/\D/', '', $phone);
        
        $this->assertEquals('5511999999999', $normalized);
    }
}