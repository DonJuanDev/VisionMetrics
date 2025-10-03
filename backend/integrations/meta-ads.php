<?php
/**
 * Meta Ads Conversions API (CAPI) Integration
 * Send server-side conversion events to Facebook/Instagram
 */

class MetaAdsIntegration {
    private $accessToken;
    private $pixelId;
    private $apiVersion = 'v18.0';
    private $testEventCode = null; // For testing events

    public function __construct($accessToken, $pixelId, $testMode = false) {
        $this->accessToken = $accessToken;
        $this->pixelId = $pixelId;
        if ($testMode) {
            $this->testEventCode = 'TEST' . rand(10000, 99999);
        }
    }

    /**
     * Send conversion event to Meta
     */
    public function sendConversion($eventName, $userData, $customData = [], $eventId = null) {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->pixelId}/events";

        $event = [
            'event_name' => $eventName, // Purchase, Lead, CompleteRegistration, etc
            'event_time' => time(),
            'event_id' => $eventId ?: uniqid('vm_', true), // For deduplication
            'event_source_url' => $userData['page_url'] ?? '',
            'action_source' => 'website',
            'user_data' => $this->hashUserData($userData),
            'custom_data' => $customData
        ];

        if ($this->testEventCode) {
            $event['test_event_code'] = $this->testEventCode;
        }

        $payload = [
            'data' => [$event],
            'access_token' => $this->accessToken
        ];

        return $this->sendRequest($url, $payload);
    }

    /**
     * Hash user data according to Meta requirements
     */
    private function hashUserData($userData) {
        $hashed = [];

        // Email - lowercase and hash
        if (!empty($userData['email'])) {
            $hashed['em'] = [hash('sha256', strtolower(trim($userData['email'])))];
        }

        // Phone - remove non-digits, hash
        if (!empty($userData['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $userData['phone']);
            $hashed['ph'] = [hash('sha256', $phone)];
        }

        // Name - lowercase, remove spaces, hash
        if (!empty($userData['name'])) {
            $nameParts = explode(' ', strtolower(trim($userData['name'])));
            if (isset($nameParts[0])) {
                $hashed['fn'] = [hash('sha256', $nameParts[0])]; // First name
            }
            if (isset($nameParts[1])) {
                $hashed['ln'] = [hash('sha256', $nameParts[1])]; // Last name
            }
        }

        // Location data
        if (!empty($userData['city'])) {
            $hashed['ct'] = [hash('sha256', strtolower(trim($userData['city'])))];
        }
        if (!empty($userData['state'])) {
            $hashed['st'] = [hash('sha256', strtolower(trim($userData['state'])))];
        }
        if (!empty($userData['country'])) {
            $hashed['country'] = [hash('sha256', strtolower(trim($userData['country'])))];
        }
        if (!empty($userData['zip'])) {
            $hashed['zp'] = [hash('sha256', preg_replace('/[^0-9]/', '', $userData['zip']))];
        }

        // IP and User Agent (not hashed)
        if (!empty($userData['ip'])) {
            $hashed['client_ip_address'] = $userData['ip'];
        }
        if (!empty($userData['user_agent'])) {
            $hashed['client_user_agent'] = $userData['user_agent'];
        }

        // FBP cookie (Facebook pixel cookie)
        if (!empty($userData['fbp'])) {
            $hashed['fbp'] = $userData['fbp'];
        }

        // FBC cookie (Facebook click ID)
        if (!empty($userData['fbc'])) {
            $hashed['fbc'] = $userData['fbc'];
        } elseif (!empty($userData['fbclid'])) {
            $hashed['fbc'] = 'fb.1.' . time() . '.' . $userData['fbclid'];
        }

        return $hashed;
    }

    /**
     * Send HTTP request to Meta API
     */
    private function sendRequest($url, $payload) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $result = [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => json_decode($response, true),
            'error' => $error
        ];

        if ($this->testEventCode) {
            $result['test_event_code'] = $this->testEventCode;
            $result['test_url'] = "https://www.facebook.com/events_manager2/list/pixel/{$this->pixelId}/test_events";
        }

        return $result;
    }

    /**
     * Quick methods for common events
     */
    public function trackLead($userData, $value = null) {
        $customData = [];
        if ($value) {
            $customData['value'] = $value;
            $customData['currency'] = 'BRL';
        }
        return $this->sendConversion('Lead', $userData, $customData);
    }

    public function trackPurchase($userData, $value, $orderId = null) {
        $customData = [
            'value' => $value,
            'currency' => 'BRL'
        ];
        if ($orderId) {
            $customData['order_id'] = $orderId;
        }
        return $this->sendConversion('Purchase', $userData, $customData);
    }

    public function trackViewContent($userData, $contentName) {
        return $this->sendConversion('ViewContent', $userData, [
            'content_name' => $contentName
        ]);
    }

    public function trackAddToCart($userData, $value) {
        return $this->sendConversion('AddToCart', $userData, [
            'value' => $value,
            'currency' => 'BRL'
        ]);
    }

    public function trackCompleteRegistration($userData) {
        return $this->sendConversion('CompleteRegistration', $userData);
    }
}

// Example usage:
/*
$meta = new MetaAdsIntegration('YOUR_ACCESS_TOKEN', 'YOUR_PIXEL_ID', true); // true = test mode

$userData = [
    'email' => 'user@example.com',
    'phone' => '+5511999999999',
    'name' => 'John Doe',
    'ip' => '192.168.1.1',
    'user_agent' => 'Mozilla/5.0...',
    'fbclid' => 'abc123',
    'page_url' => 'https://example.com'
];

$result = $meta->trackLead($userData, 100.00);
$result = $meta->trackPurchase($userData, 500.00, 'ORDER_123');
*/






