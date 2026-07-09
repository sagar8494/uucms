<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Handle the deposit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

    if ($amount >= 10) { // Minimum deposit limit of $10
        try {
            $pdo->beginTransaction();

            // Check if wallet exists
            $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $wallet = $stmt->fetch();

            if ($wallet) {
                // Update existing wallet
                $newBalance = floatval($wallet['balance']) + $amount;
                $updateStmt = $pdo->prepare("UPDATE wallets SET balance = ? WHERE user_id = ?");
                $updateStmt->execute([$newBalance, $userId]);
            } else {
                // Create wallet if it doesn't exist yet
                $newBalance = $amount;
                $insertStmt = $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, ?)");
                $insertStmt->execute([$userId, $newBalance]);
            }

            $pdo->commit();
            $message = "🎉 Success! $" . number_format($amount, 2) . " has been deposited into your wallet.";
            $messageType = "success";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $message = "Database processing error. Please try again.";
            $messageType = "error";
        }
    } else {
        $message = "Minimum deposit amount is $10.00.";
        $messageType = "error";
    }
}

// Fetch current balance to show on screen
$stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
$stmt->execute([$userId]);
$wallet = $stmt->fetch();
$balance = $wallet ? $wallet['balance'] : 0.00;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Funds - VecCricket</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #080810; color: #fff; margin: 0; padding: 0; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #11111e; padding: 15px 40px; border-bottom: 1px solid #222235; }
        .logo { font-size: 24px; font-weight: 800; color: #00e676; text-decoration: none; letter-spacing: 1px; }
        .nav-links { display: flex; gap: 20px; }
        .nav-link { color: #a0a0b5; text-decoration: none; font-weight: bold; font-size: 14px; text-transform: uppercase; }
        .nav-link:hover, .nav-link.active { color: #00e676; }
        .balance-badge { background: #1a1a2e; color: #00e676; padding: 10px 18px; border-radius: 6px; font-weight: 800; text-decoration: none; border: 1px solid rgba(0, 230, 118, 0.2); }
        
        .container { max-width: 500px; margin: 60px auto; padding: 0 20px; }
        .deposit-card { background: #11111e; border: 1px solid #222235; border-radius: 16px; padding: 40px; box-shadow: 0 12px 30px rgba(0,0,0,0.5); }
        h2 { margin: 0 0 10px 0; color: #00e676; text-transform: uppercase; font-size: 24px; }
        p.desc { color: #8e8e9f; font-size: 14px; margin-bottom: 25px; }
        
        label { font-size: 12px; color: #a0a0b5; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 8px; }
        input[type="number"] { width: 100%; padding: 15px; background: #080810; border: 1px solid #222235; color: #fff; border-radius: 8px; font-size: 16px; font-weight: bold; box-sizing: border-box; margin-bottom: 20px; }
        
        .submit-btn { width: 100%; background: linear-gradient(135deg, #00e676, #00b357); color: #080810; border: none; padding: 16px; font-weight: 800; font-size: 16px; border-radius: 8px; cursor: pointer; text-transform: uppercase; transition: 0.2s; }
        .submit-btn:hover { opacity: 0.9; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; font-size: 14px; }
        .alert-success { background: rgba(0, 230, 118, 0.1); color: #00e676; border: 1px solid rgba(0, 230, 118, 0.2); }
        .alert-error { background: rgba(255, 23, 68, 0.1); color: #ff1744; border: 1px solid rgba(255, 23, 68, 0.2); }
    </style>
</head>
<body>

<div class="navbar">
    <a href="index.php" class="logo">VecCricket</a>
    <div class="nav-links">
        <a href="index.php" class="nav-link">Sportsbook</a>
        <a href="casino.php" class="nav-link">Live Casino</a>
        <a href="andar_bahar.php" class="nav-link">Andar Bahar</a>
    </div>
    <div class="user-actions">
        <span class="balance-badge">Bal: $<?php echo number_format($balance, 2); ?></span>
    </div>
</div>

<div class="container">
    <div class="deposit-card">
        <h2>Deposit Funds</h2>
        <p class="desc">Instantly load test credits to simulate high-liquidity playing configurations.</p>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form action="deposit.php" method="POST">
            <label for="amount">Amount to Deposit ($)</label>
            <input type="number" id="amount" name="amount" min="10" step="any" placeholder="10.00" required>
            
            <button type="submit" class="submit-btn">Complete Deposit</button>
        </form>
    </div>
</div>

</body>
</html>