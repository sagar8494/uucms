<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_withdrawal'])) {
    $amount = floatval($_POST['withdraw_amount'] ?? 0);
    $payoutDetails = trim($_POST['payout_details'] ?? '');

    if ($amount < 15 || empty($payoutDetails)) {
        $message = "<p style='color: #ff1744;'>Minimum withdrawal amount is $15. Please enter full payment info.</p>";
    } else {
        try {
            $pdo->beginTransaction();

            // Lock wallet balance for verification
            $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            $wallet = $stmt->fetch();

            if (!$wallet || $wallet['balance'] < $amount) {
                $message = "<p style='color: #ff1744;'>Error: Insufficient available balance for this payout request.</p>";
                $pdo->rollBack();
            } else {
                // Deduct from available balance immediately
                $deductWallet = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ?");
                $deductWallet->execute([$amount, $userId]);

                // Log a PENDING withdrawal. We abuse the 'utr_number' column to house the payout details safely.
                $logTx = $pdo->prepare("INSERT INTO transactions (user_id, amount, utr_number, type, status) VALUES (?, ?, ?, 'withdrawal', 'pending')");
                $logTx->execute([$userId, $amount, $payoutDetails]);

                $pdo->commit();
                $message = "<p style='color: #00e676;'>Withdrawal request logged successfully! Admin will process your payout.</p>";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "<p style='color: #ff1744;'>System processing failure. Try again.</p>";
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
    $stmt->execute([$userId]);
    $wallet = $stmt->fetch();
    $currentBalance = $wallet ? $wallet['balance'] : 0.00;

    $txStmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? AND type = 'withdrawal' ORDER BY created_at DESC LIMIT 5");
    $txStmt->execute([$userId]);
    $withdrawLogs = $txStmt->fetchAll();
} catch (Exception $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Payout - Withdraw</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #0f0f1a; color: #fff; padding: 30px; margin: 0; }
        .container { max-width: 650px; margin: 0 auto; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #00e676; text-decoration: none; font-weight: bold; }
        .box { background: #161623; border: 1px solid #2e2e42; border-radius: 8px; padding: 25px; margin-bottom: 25px; }
        .balance-display { font-size: 24px; color: #00e676; font-weight: bold; margin-bottom: 20px; }
        input, textarea, button { width: 100%; padding: 12px; border: 1px solid #2e2e42; background: #0f0f1a; color: #fff; border-radius: 6px; box-sizing: border-box; margin-bottom: 15px; font-size:15px; }
        textarea { height: 80px; resize: none; font-family: sans-serif; }
        button { background: #ff1744; color: #fff; font-weight: bold; border: none; cursor: pointer; }
        button:hover { background: #b3002d; }
        table { width: 100%; border-collapse: collapse; background: #161623; border: 1px solid #2e2e42; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #2e2e42; }
        th { background-color: #0f0f1a; color: #ff1744; }
        .status-pending { color: #ffbc00; }
        .status-completed { color: #00e676; }
        .status-failed { color: #ff1744; }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php" class="back-link">← Back to Sportsbook</a>
    <h1>Secure Withdrawal Gate</h1>
    <?php echo $message; ?>

    <div class="box">
        <div class="balance-display">Available Cashout Balance: $<?php echo number_format($currentBalance, 2); ?></div>
        <form action="withdraw.php" method="POST">
            <label style="color:#a0a0a0; display:block; margin-bottom:5px;">Withdrawal Amount ($)</label>
            <input type="number" name="withdraw_amount" min="15" placeholder="Minimum $15" required>
            
            <label style="color:#a0a0a0; display:block; margin-bottom:5px;">Payout Routing Details (UPI ID, Bank Acc, IMPS)</label>
            <textarea name="payout_details" placeholder="Provide your full Bank Details, IFSC Code, or UPI ID clearly..." required></textarea>
            
            <button type="submit" name="request_withdrawal">Submit Secure Cashout Request</button>
        </form>
    </div>

    <h3>Recent Withdrawal Status History</h3>
    <table>
        <thead><tr><th>ID</th><th>Amount</th><th>Method/Details</th><th>Status</th></tr></thead>
        <tbody>
            <?php if (empty($withdrawLogs)): ?>
                <tr><td colspan="4" style="text-align: center; color:#a0a0a0;">No recent withdrawal requests logged.</td></tr>
            <?php else: ?>
                <?php foreach ($withdrawLogs as $w): ?>
                    <tr>
                        <td>#<?php echo $w['id']; ?></td>
                        <td>$<?php echo number_format($w['amount'], 2); ?></td>
                        <td style="color: #a0a0a0; font-size:13px;"><?php echo htmlspecialchars($w['utr_number']); ?></td>
                        <td><span class="status-<?php echo $w['status']; ?>">● <?php echo strtoupper($w['status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>