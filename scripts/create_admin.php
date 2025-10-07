#!/usr/bin/env php
<?php
/**
 * VisionMetrics - Create Admin Account
 * 
 * Creates initial admin account from .env credentials
 * 
 * Usage:
 * php scripts/create_admin.php
 * 
 * OR with custom credentials:
 * php scripts/create_admin.php admin@example.com strongpassword123
 * 
 * Requirements:
 * - ADMIN_EMAIL and ADMIN_PASS in .env file (if not provided as arguments)
 * - Database connection configured
 */

// Ensure CLI mode
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line.');
}

// Bootstrap application
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../backend/config.php';

echo "═══════════════════════════════════════════════════════════\n";
echo "VisionMetrics - Admin Account Creator\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// ═══════════════════════════════════════════════════════════
// GET CREDENTIALS
// ═══════════════════════════════════════════════════════════
$email = $argv[1] ?? getenv('ADMIN_EMAIL');
$password = $argv[2] ?? getenv('ADMIN_PASS');

if (empty($email) || empty($password)) {
    echo "❌ ERROR: Admin credentials not provided.\n\n";
    echo "Please either:\n";
    echo "1. Set ADMIN_EMAIL and ADMIN_PASS in your .env file, OR\n";
    echo "2. Provide them as arguments: php scripts/create_admin.php email@example.com password123\n\n";
    exit(1);
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "❌ ERROR: Invalid email address: {$email}\n\n";
    exit(1);
}

// Validate password strength
if (strlen($password) < 6) {
    echo "❌ ERROR: Password must be at least 6 characters long.\n\n";
    exit(1);
}

echo "Creating admin account...\n";
echo "Email: {$email}\n\n";

try {
    $db = getDB();
    
    // ═══════════════════════════════════════════════════════════
    // CHECK IF USER ALREADY EXISTS
    // ═══════════════════════════════════════════════════════════
    $stmt = $db->prepare("SELECT id, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        echo "⚠️  WARNING: User with email {$email} already exists.\n";
        echo "User ID: {$existingUser['id']}\n\n";
        
        echo "Do you want to update the password? [y/N]: ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        $confirm = trim(strtolower($line));
        fclose($handle);
        
        if ($confirm !== 'y' && $confirm !== 'yes') {
            echo "\n✅ Operation cancelled. Existing user unchanged.\n\n";
            exit(0);
        }
        
        // Update password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$passwordHash, $existingUser['id']]);
        
        $userId = $existingUser['id'];
        
        echo "✅ Password updated successfully!\n\n";
    } else {
        // ═══════════════════════════════════════════════════════════
        // CREATE NEW USER
        // ═══════════════════════════════════════════════════════════
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $name = explode('@', $email)[0]; // Use email prefix as default name
        $name = ucfirst($name);
        
        $stmt = $db->prepare("
            INSERT INTO users (email, password_hash, name, email_verified_at, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW(), NOW())
        ");
        $stmt->execute([$email, $passwordHash, $name]);
        
        $userId = $db->lastInsertId();
        
        echo "✅ User created successfully!\n";
        echo "User ID: {$userId}\n";
        echo "Name: {$name}\n\n";
    }
    
    // ═══════════════════════════════════════════════════════════
    // CREATE OR FIND WORKSPACE
    // ═══════════════════════════════════════════════════════════
    $stmt = $db->prepare("
        SELECT w.* FROM workspaces w
        WHERE w.owner_id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $workspace = $stmt->fetch();
    
    if ($workspace) {
        $workspaceId = $workspace['id'];
        $workspaceName = $workspace['name'];
        
        echo "📦 Using existing workspace:\n";
        echo "Workspace ID: {$workspaceId}\n";
        echo "Workspace Name: {$workspaceName}\n\n";
    } else {
        // Create default workspace
        $workspaceName = "{$name}'s Workspace";
        $workspaceSlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name)) . '-' . substr(md5($email), 0, 6);
        
        $stmt = $db->prepare("
            INSERT INTO workspaces (name, slug, owner_id, plan, status, created_at, updated_at)
            VALUES (?, ?, ?, 'free', 'active', NOW(), NOW())
        ");
        $stmt->execute([$workspaceName, $workspaceSlug, $userId]);
        
        $workspaceId = $db->lastInsertId();
        
        echo "✅ Workspace created successfully!\n";
        echo "Workspace ID: {$workspaceId}\n";
        echo "Workspace Name: {$workspaceName}\n";
        echo "Workspace Slug: {$workspaceSlug}\n\n";
        
        // Add user as workspace owner
        $stmt = $db->prepare("
            INSERT INTO workspace_members (workspace_id, user_id, role, created_at)
            VALUES (?, ?, 'owner', NOW())
        ");
        $stmt->execute([$workspaceId, $userId]);
    }
    
    // ═══════════════════════════════════════════════════════════
    // DISPLAY SUCCESS MESSAGE
    // ═══════════════════════════════════════════════════════════
    echo "═══════════════════════════════════════════════════════════\n";
    echo "✅ ADMIN ACCOUNT READY\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    echo "Login credentials:\n";
    echo "─────────────────────────────────────────────────────────\n";
    echo "Email:     {$email}\n";
    echo "Password:  ********** (as configured)\n";
    echo "User ID:   {$userId}\n";
    echo "Workspace: {$workspaceName} (ID: {$workspaceId})\n";
    echo "─────────────────────────────────────────────────────────\n\n";
    
    $appUrl = getenv('APP_URL') ?: 'http://localhost';
    $loginUrl = rtrim($appUrl, '/') . '/backend/login.php';
    
    echo "🌐 Login URL: {$loginUrl}\n\n";
    
    echo "🔒 SECURITY REMINDER:\n";
    echo "- Change your password after first login\n";
    echo "- Remove ADMIN_PASS from .env file after setup\n";
    echo "- Enable 2FA if available\n\n";
    
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



