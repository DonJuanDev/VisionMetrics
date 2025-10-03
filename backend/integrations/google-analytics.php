<?php
/**
 * Google Analytics 4 (GA4) Measurement Protocol Integration
 * Send events server-side to GA4
 */

class GoogleAnalytics4Integration {
    private $measurementId;
    private $apiSecret;
    private $endpoint = 'https://www.google-analytics.com/mp/collect';
    private $debug = false;

    public function __construct($measurementId, $apiSecret, $debug = false) {
        $this->measurementId = $measurementId; // G-XXXXXXXXXX
        $this->apiSecret = $apiSecret;
        $this->debug = $debug;
        
        if ($debug) {
            $this->endpoint = 'https://www.google-analytics.com/debug/mp/collect';
        }
    }

    /**
     * Send event to GA4
     */
    public function sendEvent($clientId, $eventName, $parameters = []) {
        $url = $this->endpoint . '?measurement_id=' . $this->measurementId . '&api_secret=' . $this->apiSecret;

        $payload = [
            'client_id' => $clientId, // Unique user ID
            'events' => [
                [
                    'name' => $eventName,
                    'params' => $parameters
                ]
            ]
        ];

        // Add user properties if available
        if (!empty($parameters['email'])) {
            $payload['user_properties'] = [
                'email' => ['value' => $parameters['email']]
            ];
        }

        return $this->sendRequest($url, $payload);
    }

    /**
     * Track page view
     */
    public function trackPageView($clientId, $pageLocation, $pageTitle = '', $additionalParams = []) {
        $params = array_merge([
            'page_location' => $pageLocation,
            'page_title' => $pageTitle,
            'engagement_time_msec' => '100'
        ], $additionalParams);

        return $this->sendEvent($clientId, 'page_view', $params);
    }

    /**
     * Track conversion/lead
     */
    public function trackConversion($clientId, $value = null, $currency = 'BRL', $additionalParams = []) {
        $params = $additionalParams;
        
        if ($value !== null) {
            $params['value'] = $value;
            $params['currency'] = $currency;
        }

        return $this->sendEvent($clientId, 'conversion', $params);
    }

    /**
     * Track purchase
     */
    public function trackPurchase($clientId, $transactionId, $value, $currency = 'BRL', $items = []) {
        $params = [
            'transaction_id' => $transactionId,
            'value' => $value,
            'currency' => $currency,
            'items' => $items
        ];

        return $this->sendEvent($clientId, 'purchase', $params);
    }

    /**
     * Track lead generation
     */
    public function trackLead($clientId, $value = null, $source = '', $medium = '', $campaign = '') {
        $params = [];
        
        if ($value) {
            $params['value'] = $value;
            $params['currency'] = 'BRL';
        }
        
        if ($source) $params['source'] = $source;
        if ($medium) $params['medium'] = $medium;
        if ($campaign) $params['campaign'] = $campaign;

        return $this->sendEvent($clientId, 'generate_lead', $params);
    }

    /**
     * Track custom event
     */
    public function trackCustom($clientId, $eventName, $parameters = []) {
        return $this->sendEvent($clientId, $eventName, $parameters);
    }

    /**
     * Send request to GA4
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

        if ($this->debug && isset($result['response']['validationMessages'])) {
            error_log('GA4 Validation: ' . print_r($result['response']['validationMessages'], true));
        }

        return $result;
    }

    /**
     * Batch send multiple events
     */
    public function sendBatch($clientId, $events) {
        $url = $this->endpoint . '?measurement_id=' . $this->measurementId . '&api_secret=' . $this->apiSecret;

        $formattedEvents = [];
        foreach ($events as $event) {
            $formattedEvents[] = [
                'name' => $event['name'],
                'params' => $event['params'] ?? []
            ];
        }

        $payload = [
            'client_id' => $clientId,
            'events' => $formattedEvents
        ];

        return $this->sendRequest($url, $payload);
    }

    /**
     * Track e-commerce events
     */
    public function trackAddToCart($clientId, $value, $items = []) {
        return $this->sendEvent($clientId, 'add_to_cart', [
            'value' => $value,
            'currency' => 'BRL',
            'items' => $items
        ]);
    }

    public function trackBeginCheckout($clientId, $value, $items = []) {
        return $this->sendEvent($clientId, 'begin_checkout', [
            'value' => $value,
            'currency' => 'BRL',
            'items' => $items
        ]);
    }

    public function trackAddPaymentInfo($clientId, $value, $paymentType = '') {
        return $this->sendEvent($clientId, 'add_payment_info', [
            'value' => $value,
            'currency' => 'BRL',
            'payment_type' => $paymentType
        ]);
    }
}

// Example usage:
/*
// Initialize
$ga4 = new GoogleAnalytics4Integration('G-XXXXXXXXXX', 'YOUR_API_SECRET', true);

// Track page view
$ga4->trackPageView(
    'client_id_123',
    'https://example.com/products',
    'Products Page',
    ['utm_source' => 'google', 'utm_campaign' => 'summer_sale']
);

// Track lead
$ga4->trackLead('client_id_123', 100.00, 'google', 'cpc', 'summer_sale');

// Track purchase
$ga4->trackPurchase('client_id_123', 'ORDER_789', 299.90, 'BRL', [
    ['item_id' => 'SKU_001', 'item_name' => 'Product A', 'price' => 299.90]
]);
*/






