<?php
/**
 * Rate Limiter - Redis based
 * Protege endpoints de tracking contra abuse
 */

class RateLimiter {
    private $redis;
    private $enabled;
    private $maxRequests;
    private $window;
    
    public function __construct() {
        $this->redis = getRedis();
        $this->enabled = filter_var(env('RATE_LIMIT_ENABLED', true), FILTER_VALIDATE_BOOLEAN);
        $this->maxRequests = (int)env('RATE_LIMIT_MAX_REQUESTS', 100);
        $this->window = (int)env('RATE_LIMIT_WINDOW', 60);
    }
    
    public function check($identifier) {
        if (!$this->enabled || $this->redis === null) {
            return true;
        }
        
        $key = 'rate_limit:' . $identifier;
        $current = $this->redis->get($key);
        
        if ($current === false) {
            $this->redis->setex($key, $this->window, 1);
            return true;
        }
        
        if ((int)$current >= $this->maxRequests) {
            logMessage('WARNING', 'Rate limit exceeded', [
                'identifier' => $identifier,
                'current' => $current,
                'max' => $this->maxRequests
            ]);
            return false;
        }
        
        $this->redis->incr($key);
        return true;
    }
    
    public function remaining($identifier) {
        if (!$this->enabled || $this->redis === null) {
            return $this->maxRequests;
        }
        
        $key = 'rate_limit:' . $identifier;
        $current = (int)($this->redis->get($key) ?? 0);
        
        return max(0, $this->maxRequests - $current);
    }
}

function checkRateLimit($identifier = null) {
    if ($identifier === null) {
        $identifier = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    $limiter = new RateLimiter();
    
    if (!$limiter->check($identifier)) {
        http_response_code(429);
        json_response([
            'error' => 'Rate limit exceeded',
            'retry_after' => (int)env('RATE_LIMIT_WINDOW', 60)
        ], 429);
    }
    
    return true;
}



