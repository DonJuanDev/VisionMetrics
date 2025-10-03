<?php
/**
 * Database connection wrapper
 * PDO with prepared statements
 */

function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                DB_HOST,
                $_ENV['DB_PORT'] ?? 3306,
                DB_NAME
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            logMessage('ERROR', 'Database connection failed', ['error' => $e->getMessage()]);
            
            if (APP_DEBUG) {
                die('Database connection failed: ' . $e->getMessage());
            }
            
            http_response_code(503);
            die('Service temporarily unavailable');
        }
    }
    
    return $pdo;
}

function getRedis() {
    static $redis = null;
    
    if ($redis === null) {
        try {
            $redis = new Redis();
            $redis->connect(REDIS_HOST, REDIS_PORT);
            
            if (!empty($_ENV['REDIS_PASSWORD'])) {
                $redis->auth($_ENV['REDIS_PASSWORD']);
            }
            
        } catch (Exception $e) {
            logMessage('WARNING', 'Redis connection failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    return $redis;
}



