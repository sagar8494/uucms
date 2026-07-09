<?php
require_once 'config/db.php';

// Configure your desired admin credentials here
$adminUsername = 'masteradmin';
$adminPassword = 'AdminPass123'; // Change this to whatever password you want

try {
    // Check if the admin user already exists to prevent duplicates
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$adminUsername]);
    
    if ($stmt->fetch()) {
        echo "<h3 style='color:#ff1744;'>An account with the username '$adminUsername' already exists!</h3>";
    } else {
        // Securely hash the password so login.php can read it
        $hashedPassword = password_hash($adminPassword, PASSWORD_BCRYPT);
        
        // Insert the master user account with the 'admin' role privilege
        $insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        $insert->execute([$adminUsername, $hashedPassword]);
        
        // Also auto-provision a master system wallet for financial testing
        $newAdminId = $pdo->lastInsertId();
        $wallet = $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, 100000.00)");
        $wallet->execute([$newAdminId]);

        echo "<div style='font-family:sans-serif; background:#161623; color:#fff; padding:30px; border-radius:8px; max-width:500px; margin:50px auto; border:1px solid #00e676;'>
                <h2 style='color:#00e676; margin-top:0;'>🚀 Admin Created Successfully!</h2>
                <p>An authorized administrator profile has been injected into your local database layers.</p>
                <hr style='border-color:#2e2e42;'>
                <p><strong>Username:</strong> <code style='color:#ffbc00;'>$adminUsername</code></p>
                <p><strong>Password:</strong> <code style='color:#ffbc00;'>$adminPassword</code></p>
                <p style='color:#a0a0a0; font-size:13px;'>⚠️ <em>For security, delete the 'make_admin.php' file from your directory immediately after running it.</em></p>
              </div>";
    }
} catch (Exception $e) {
    echo "<h3 style='color:#ff1744;'>Database Error: " . $e->getMessage() . "</h3>";
}
?>