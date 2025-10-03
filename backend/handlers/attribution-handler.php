<?php
// Attribution calculation handler

function calculateAttribution($workspaceId, $model, $db) {
    $stmt = $db->prepare("
        SELECT 
            c.id as conversion_id,
            c.sale_value,
            c.utm_source as last_source,
            l.id as lead_id
        FROM conversations c
        INNER JOIN leads l ON c.lead_id = l.id
        WHERE c.workspace_id = ? AND c.is_sale = 1
    ");
    $stmt->execute([$workspaceId]);
    $conversions = $stmt->fetchAll();
    
    $attribution = [];
    
    foreach ($conversions as $conversion) {
        $stmt = $db->prepare("
            SELECT DISTINCT utm_source, created_at
            FROM events
            WHERE lead_id = ? AND utm_source IS NOT NULL
            ORDER BY created_at ASC
        ");
        $stmt->execute([$conversion['lead_id']]);
        $touchpoints = $stmt->fetchAll();
        
        if (empty($touchpoints)) {
            $touchpoints = [['utm_source' => $conversion['last_source'] ?? 'direct', 'created_at' => null]];
        }
        
        $value = $conversion['sale_value'];
        $credit = distributeCredit($touchpoints, $value, $model);
        
        foreach ($credit as $source => $amount) {
            if (!isset($attribution[$source])) {
                $attribution[$source] = ['source' => $source, 'conversions' => 0, 'value' => 0];
            }
            $attribution[$source]['conversions']++;
            $attribution[$source]['value'] += $amount;
        }
    }
    
    usort($attribution, fn($a, $b) => $b['value'] <=> $a['value']);
    return $attribution;
}

function distributeCredit($touchpoints, $totalValue, $model) {
    $credit = [];
    $count = count($touchpoints);
    
    if ($count === 0) return ['direct' => $totalValue];
    
    switch ($model) {
        case 'first_touch':
            $credit[$touchpoints[0]['utm_source'] ?? 'direct'] = $totalValue;
            break;
            
        case 'last_touch':
            $credit[$touchpoints[$count - 1]['utm_source'] ?? 'direct'] = $totalValue;
            break;
            
        case 'linear':
            $valuePerTouch = $totalValue / $count;
            foreach ($touchpoints as $tp) {
                $source = $tp['utm_source'] ?? 'direct';
                $credit[$source] = ($credit[$source] ?? 0) + $valuePerTouch;
            }
            break;
            
        case 'time_decay':
            $weights = [];
            $totalWeight = 0;
            $lastDate = strtotime($touchpoints[$count - 1]['created_at']);
            
            foreach ($touchpoints as $tp) {
                $touchDate = strtotime($tp['created_at']);
                $daysSince = max(0, ($lastDate - $touchDate) / 86400);
                $weight = pow(2, -$daysSince / 7);
                $weights[] = $weight;
                $totalWeight += $weight;
            }
            
            foreach ($touchpoints as $i => $tp) {
                $source = $tp['utm_source'] ?? 'direct';
                $creditValue = ($weights[$i] / $totalWeight) * $totalValue;
                $credit[$source] = ($credit[$source] ?? 0) + $creditValue;
            }
            break;
            
        case 'position_based':
            if ($count === 1) {
                $credit[$touchpoints[0]['utm_source'] ?? 'direct'] = $totalValue;
            } else {
                $first = $touchpoints[0]['utm_source'] ?? 'direct';
                $last = $touchpoints[$count - 1]['utm_source'] ?? 'direct';
                
                $credit[$first] = ($credit[$first] ?? 0) + ($totalValue * 0.4);
                $credit[$last] = ($credit[$last] ?? 0) + ($totalValue * 0.4);
                
                if ($count > 2) {
                    $middleValue = ($totalValue * 0.2) / ($count - 2);
                    for ($i = 1; $i < $count - 1; $i++) {
                        $source = $touchpoints[$i]['utm_source'] ?? 'direct';
                        $credit[$source] = ($credit[$source] ?? 0) + $middleValue;
                    }
                }
            }
            break;
            
        case 'last_non_direct':
            for ($i = $count - 1; $i >= 0; $i--) {
                $source = $touchpoints[$i]['utm_source'];
                if ($source && $source !== 'direct') {
                    $credit[$source] = $totalValue;
                    break;
                }
            }
            if (empty($credit)) $credit['direct'] = $totalValue;
            break;
    }
    
    return $credit;
}

$attributionData = calculateAttribution($currentWorkspace['id'], $selectedModel, $db);





