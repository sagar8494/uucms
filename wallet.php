<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_deposit'])) {
    $amount = floatval($_POST['deposit_amount'] ?? 0);
    $utr = trim($_POST['utr_number'] ?? '');
    
    if ($amount < 10 || empty($utr)) {
        $message = "<p style='color: #ff1744;'>Please fill all fields accurately. Minimum deposit is $10.</p>";
    } else {
        try {
            // 1. Check if UTR already exists in the database
            $checkUtr = $pdo->prepare("SELECT id FROM transactions WHERE utr_number = ?");
            $checkUtr->execute([$utr]);
            if ($checkUtr->fetch()) {
                $message = "<p style='color: #ff1744;'>Error: This UTR / Transaction number has already been used!</p>";
            } else {
                // 2. Process file upload safely
                $screenshotPath = "";
                if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === 0) {
                    $allowed = ['jpg', 'jpeg', 'png'];
                    $filename = $_FILES['screenshot']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed)) {
                        // Create unique name to avoid overwriting files
                        $newName = "TX_" . time() . "_" . uniqid() . "." . $ext;
                        $targetDir = "uploads/";
                        if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $targetDir . $newName)) {
                            $screenshotPath = $targetDir . $newName;
                        }
                    }
                }

                if (empty($screenshotPath)) {
                    $message = "<p style='color: #ff1744;'>Error uploading proof screenshot. Please use JPG or PNG.</p>";
                } else {
                    // 3. Log a PENDING deposit request for admin approval
                    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, utr_number, screenshot, type, status) VALUES (?, ?, ?, ?, 'deposit', 'pending')");
                    $stmt->execute([$userId, $amount, $utr, $screenshotPath]);
                    $message = "<p style='color: #00e676;'>Request submitted! Funds will show once admin confirms your UTR.</p>";
                }
            }
        } catch (Exception $e) {
            $message = "<p style='color: #ff1744;'>System processing failure. Try again.</p>";
        }
    }
}

// Fetch balances and logs
try {
    $stmt = $pdo->prepare("SELECT balance, frozen_balance FROM wallets WHERE user_id = ?");
    $stmt->execute([$userId]);
    $wallet = $stmt->fetch();

    $txStmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $txStmt->execute([$userId]);
    $transactions = $txStmt->fetchAll();
} catch (Exception $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deposit Funds - Wallet</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #1a1a2e; color: #fff; padding: 30px; margin: 0; }
        .container { max-width: 800px; margin: 0 auto; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #00e676; text-decoration: none; font-weight: bold; }
        .wallet-grid { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { flex: 1; background: #161623; border: 1px solid #2e2e42; padding: 20px; border-radius: 8px; text-align: center; }
        .card h2 { margin: 0 0 10px; color: #a0a0a0; font-size: 16px; }
        .card .amount { font-size: 28px; font-weight: bold; color: #00e676; }
        .card .amount.frozen { color: #ff1744; }
        .deposit-section { display: flex; gap: 20px; background: #161623; border: 1px solid #2e2e42; border-radius: 8px; padding: 25px; margin-bottom: 30px; }
        .qr-side { flex: 1; text-align: center; border-right: 1px solid #2e2e42; padding-right: 20px; }
        .form-side { flex: 1; padding-left: 10px; }
        input, button { width: 100%; padding: 12px; border: 1px solid #2e2e42; background: #0f0f1a; color: #fff; border-radius: 6px; box-sizing: border-box; margin-bottom: 15px; }
        button { background: #00e676; color: #1a1a2e; font-weight: bold; border: none; cursor: pointer; font-size: 16px; }
        button:hover { background: #00b357; }
        table { width: 100%; border-collapse: collapse; background: #161623; border: 1px solid #2e2e42; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #2e2e42; }
        th { background-color: #0f0f1a; color: #00e676; }
        .status-pending { color: #ffbc00; }
        .status-completed { color: #00e676; }
        .status-failed { color: #ff1744; }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php" class="back-link">← Back to Dashboard</a>
    <h1>Account Wallet & Deposits</h1>
    <?php echo $message; ?>

    <div class="wallet-grid">
        <div class="card"><h2>Available Balance</h2><div class="amount">$<?php echo number_format($wallet['balance'] ?? 0.00, 2); ?></div></div>
        <div class="card"><h2>Frozen in Bets</h2><div class="amount frozen">$<?php echo number_format($wallet['frozen_balance'] ?? 0.00, 2); ?></div></div>
    </div>

    <div class="deposit-section">
        <div class="qr-side">
            <h3 style="color: #00e676; margin-top:0;">Scan to Pay</h3>
            <div style="background: #fff; padding: 10px; display: inline-block; border-radius: 8px; margin-bottom: 10px;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=SamplePaymentGateway" alt="Payment QR Code" width="180" height="180">
            </div>
            <p style="font-size: 13px; color: #a0a0a0; margin:0;">Scan using any banking app. Once transferred, submit proof details on the right.</p>
        </div>
        <div class="form-side">
            <h3 style="margin-top:0;">Submit Payment Details</h3>
            <form action="wallet.php" method="POST" enctype="multipart/form-data">
                <input type="number" name="deposit_amount" min="10" placeholder="Amount Sent ($)" required>
                <input type="text" name="utr_number" placeholder="Enter 12-Digit UTR / Reference ID" required>
                <label style="display:block; font-size:13px; margin-bottom:5px; color:#a0a0a0;">Upload Payment Screenshot:</label>
                <input type="file" name="screenshot" accept="image/*" required>
                <button type="submit" name="submit_deposit">Submit Deposit Claim</button>
            </form>
        </div>
    </div>

    <h3>Recent Transactions</h3>
    <table>
        <thead><tr><th>ID</th><th>Type</th><th>Amount</th><th>UTR Number</th><th>Status</th></tr></thead>
        <tbody>
            <?php if (empty($transactions)): ?>
                <tr><td colspan="5" style="text-align: center;">No transaction history found.</td></tr>
            <?php else: ?>
                <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td>#<?php echo $tx['id']; ?></td>
                        <td><strong><?php echo strtoupper($tx['type']); ?></strong></td>
                        <td>$<?php echo number_format($tx['amount'], 2); ?></td>
                        <td style="color: #a0a0a0;"><?php echo htmlspecialchars($tx['utr_number'] ?? 'N/A'); ?></td>
                        <td><span class="status-<?php echo $tx['status']; ?>">● <?php echo strtoupper($tx['status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>