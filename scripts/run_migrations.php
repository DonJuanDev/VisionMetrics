#!/usr/bin/env php
<?php
/**
 * VisionMetrics - Database Migration Runner
 * 
 * Automatically runs all SQL migrations in order
 * 
 * Usage:
 * php scripts/run_migrations.php
 * 
 * OR with specific migration:
 * php scripts/run_migrations.php 20251006_hostinger_prod_tables.sql
 */

// Ensure CLI mode
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line.');
}

// Bootstrap application
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../backend/config.php';

echo "═══════════════════════════════════════════════════════════\n";
echo "VisionMetrics - Database Migration Runner\n";
echo "═══════════════════════════════════════════════════════════\n\n";

try {
    $db = getDB();
    
    // Create migrations tracking table if not exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_migration (migration)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Get list of already executed migrations
    $stmt = $db->query("SELECT migration FROM migrations ORDER BY executed_at");
    $executedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Already executed migrations: " . count($executedMigrations) . "\n\n";
    
    // Get migration files
    $migrationsDir = __DIR__ . '/../sql/migrations/';
    
    if (!is_dir($migrationsDir)) {
        echo "❌ ERROR: Migrations directory not found: {$migrationsDir}\n\n";
        exit(1);
    }
    
    $files = glob($migrationsDir . '*.sql');
    sort($files);
    
    if (empty($files)) {
        echo "⚠️  No migration files found in {$migrationsDir}\n\n";
        exit(0);
    }
    
    echo "Found " . count($files) . " migration file(s)\n\n";
    
    // Check if specific migration requested
    $specificMigration = $argv[1] ?? null;
    
    $executed = 0;
    $skipped = 0;
    
    foreach ($files as $file) {
        $filename = basename($file);
        
        // Skip if specific migration requested and this isn't it
        if ($specificMigration && $filename !== $specificMigration) {
            continue;
        }
        
        // Check if already executed
        if (in_array($filename, $executedMigrations)) {
            echo "⏭️  SKIP: {$filename} (already executed)\n";
            $skipped++;
            continue;
        }
        
        echo "🔄 Running: {$filename}\n";
        
        // Read SQL file
        $sql = file_get_contents($file);
        
        if (empty($sql)) {
            echo "   ⚠️  WARNING: Empty migration file\n";
            continue;
        }
        
        // Begin transaction
        $db->beginTransaction();
        
        try {
            // Execute SQL (handle multiple statements)
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($stmt) {
                    return !empty($stmt) && 
                           stripos($stmt, 'SET FOREIGN_KEY_CHECKS') === false &&
                           stripos($stmt, 'SET NAMES') === false;
                }
            );
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $db->exec($statement . ';');
                }
            }
            
            // Record migration
            $stmt = $db->prepare("INSERT INTO migrations (migration) VALUES (?)");
            $stmt->execute([$filename]);
            
            // Commit transaction
            $db->commit();
            
            echo "   ✅ SUCCESS\n";
            $executed++;
            
        } catch (PDOException $e) {
            // Rollback on error
            $db->rollBack();
            
            echo "   ❌ ERROR: {$e->getMessage()}\n";
            
            // Continue with other migrations or stop?
            echo "\n   Continue with remaining migrations? [y/N]: ";
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            $confirm = trim(strtolower($line));
            fclose($handle);
            
            if ($confirm !== 'y' && $confirm !== 'yes') {
                echo "\n⛔ Migration process stopped.\n\n";
                exit(1);
            }
            
            echo "\n";
        }
    }
    
    echo "\n═══════════════════════════════════════════════════════════\n";
    echo "Migration Summary\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "Executed: {$executed}\n";
    echo "Skipped:  {$skipped}\n";
    echo "Total:    " . count($files) . "\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    if ($executed > 0) {
        echo "✅ Migrations completed successfully!\n\n";
    } else {
        echo "ℹ️  No new migrations to run.\n\n";
    }
    
    exit(0);
    
} catch (PDOException $e) {
    echo "❌ DATABASE ERROR: {$e->getMessage()}\n\n";
    
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "💡 Make sure your database server is running and credentials in .env are correct.\n\n";
    }
    
    exit(1);
} catch (Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n\n";
    exit(1);
}



