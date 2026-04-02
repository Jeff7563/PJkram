<?php
/**
 * migrate_passwords.php
 * Script for migrating plain-text passwords to hashed passwords (BCRYPT) 
 * for PDPA compliance and security.
 */

if (php_sapi_name() !== 'cli' && !isset($_GET['run'])) {
    die("This script must be run from CMD or by adding ?run=1 to the URL.");
}

require_once __DIR__ . '/../shared/config/db_connect.php';

try {
    $pdo = getServiceCenterPDO();
    
    // Get all users
    $stmt = $pdo->query("SELECT id, password FROM users");
    $users = $stmt->fetchAll();
    
    $count = 0;
    foreach ($users as $user) {
        $id = $user['id'];
        $pwd = $user['password'];
        
        // Check if already hashed
        $info = password_get_info($pwd);
        if ($info['algo'] !== 0) {
            // Already hashed, skip
            continue;
        }
        
        // Hash it
        $hashed = password_hash($pwd, PASSWORD_BCRYPT);
        
        // Update DB
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$hashed, $id]);
        $count++;
    }
    
    echo "Successfully migrated $count passwords to BCrypt hash.\n";
    
} catch (Exception $e) {
    die("Error during migration: " . $e->getMessage());
}
