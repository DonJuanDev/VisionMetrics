<?php
/**
 * PHPUnit Bootstrap
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load test environment
$_ENV['APP_ENV'] = 'testing';
$_ENV['APP_DEBUG'] = 'true';
$_ENV['DB_HOST'] = 'mysql';
$_ENV['DB_NAME'] = 'visionmetrics_test';
$_ENV['DB_USER'] = 'visionmetrics';
$_ENV['DB_PASS'] = 'visionmetrics';
$_ENV['REDIS_HOST'] = 'redis';
$_ENV['ADAPTER_MODE'] = 'simulate';

// Bootstrap app
require_once __DIR__ . '/../src/bootstrap.php';