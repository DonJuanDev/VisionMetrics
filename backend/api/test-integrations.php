<?php
require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../integrations/meta-ads.php';
require_once __DIR__ . '/../integrations/google-analytics.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$value = floatval($input['value'] ?? 0);
$testMeta = $input['test_meta'] ?? false;
$testGa4 = $input['test_ga4'] ?? false;

$results = ['success' => true];

// Test Meta Ads
if ($testMeta) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM integrations WHERE workspace_id = ? AND type = 'meta_ads' AND is_active = 1");
    $stmt->execute([$currentWorkspace['id']]);
    $metaConfig = $stmt->fetch();
    
    if ($metaConfig) {
        $credentials = json_decode($metaConfig['credentials'], true);
        $settings = json_decode($metaConfig['settings'], true);
        
        $meta = new MetaAdsIntegration(
            $credentials['access_token'],
            $credentials['pixel_id'],
            $settings['test_mode'] ?? false
        );
        
        $userData = [
            'email' => $email,
            'name' => 'Test User',
            'phone' => '+5511999999999',
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'page_url' => APP_URL . '/test'
        ];
        
        $results['meta_result'] = $meta->trackLead($userData, $value);
    } else {
        $results['meta_result'] = ['success' => false, 'error' => 'Meta Ads não configurado'];
    }
}

// Test GA4
if ($testGa4) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM integrations WHERE workspace_id = ? AND type = 'google_analytics' AND is_active = 1");
    $stmt->execute([$currentWorkspace['id']]);
    $ga4Config = $stmt->fetch();
    
    if ($ga4Config) {
        $credentials = json_decode($ga4Config['credentials'], true);
        
        $ga4 = new GoogleAnalytics4Integration(
            $credentials['measurement_id'],
            $credentials['api_secret'],
            true // debug mode
        );
        
        $clientId = 'test_' . uniqid();
        
        $results['ga4_result'] = $ga4->trackLead($clientId, $value, 'test', 'api', 'test_campaign');
    } else {
        $results['ga4_result'] = ['success' => false, 'error' => 'GA4 não configurado'];
    }
}

json_response($results);






