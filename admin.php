<?php
session_start();
require_once 'config/db.php';

// Verify user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// SECURE ROLE CHECK: Query the database to verify the user is actually an administrator
$checkRole = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$checkRole->execute([$_SESSION['user_id']]);
$userProfile = $checkRole->fetch();

if (!$userProfile || $userProfile['role'] !== 'admin') {
    http_response_code(403);
    echo "<div style='background:#1a1a2e; color:#ff1744; height:100vh; display:flex; flex-direction:column; justify-content:center; align-items:center; font-family:sans-serif;'>
            <h1>🚫 Access Denied (403 Error)</h1>
            <p style='color:#a0a0a0;'>You do not have administrative privileges to access this secure terminal gateway.</p>
            <a href='index.php' style='color:#00e676; text-decoration:none; font-weight:bold; border:1px solid #00e676; padding:8px 16px; border-radius:4px;'>Return to Sportsbook Dashboard</a>
          </div>";
    exit;
}

$message = "";

// 1. Process Manual Withdrawal Payout Actions
if (isset($_POST['action_payout'])) {
    $txId = intval($_POST['tx_id']);
    $action = $_POST['action_type'];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND type = 'withdrawal' AND status = 'pending' FOR UPDATE");
        $stmt->execute([$txId]);
        $tx = $stmt->fetch();

        if ($tx) {
            if ($action === 'approve') {
                $updateTx = $pdo->prepare("UPDATE transactions SET status = 'completed' WHERE id = ?");
                $updateTx->execute([$txId]);
                $message = "<p style='color: #00e676;'>Withdrawal #$txId marked as PAID successfully.</p>";
            } else {
                $returnFunds = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?");
                $returnFunds->execute([$tx['amount'], $tx['user_id']]);

                $updateTx = $pdo->prepare("UPDATE transactions SET status = 'failed' WHERE id = ?");
                $updateTx->execute([$txId]);
                $message = "<p style='color: #ff1744;'>Withdrawal #$txId rejected. Funds returned to client.</p>";
            }
        }
        $pdo->commit();
    } catch (Exception $e) { 
        $pdo->rollBack(); 
        $message = "<p style='color: #ff1744;'>Payout routing error.</p>"; 
    }
}

// 2. Process Manual Deposit Approvals
if (isset($_POST['action_deposit'])) {
    $txId = intval($_POST['tx_id']); 
    $action = $_POST['action_type'];
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND type = 'deposit' AND status = 'pending' FOR UPDATE");
        $stmt->execute([$txId]); 
        $transaction = $stmt->fetch();
        if ($transaction) {
            if ($action === 'accept') {
                $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?")->execute([$transaction['amount'], $transaction['user_id']]);
                $pdo->prepare("UPDATE transactions SET status = 'completed' WHERE id = ?")->execute([$txId]);
                $message = "<p style='color: #00e676;'>Deposit approved!</p>";
            } else {
                $pdo->prepare("UPDATE transactions SET status = 'failed' WHERE id = ?")->execute([$txId]);
                $message = "<p style='color: #ff1744;'>Deposit declined.</p>";
            }
        }
        $pdo->commit();
    } catch (Exception $e) { 
        $pdo->rollBack(); 
        $message = "<p style='color: #ff1744;'>System routing failure.</p>"; 
    }
}

// 3. QR Deployments
if (isset($_POST['upload_qr'])) {
    $displayName = trim($_POST['qr_name'] ?? 'Payment QR');
    if (isset($_FILES['qr_file']) && $_FILES['qr_file']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['qr_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $newName = "QR_" . time() . "_" . uniqid() . "." . $ext;
            if (move_uploaded_file($_FILES['qr_file']['tmp_name'], "uploads/" . $newName)) {
                if (isset($_POST['set_active_now'])) { 
                    $pdo->query("UPDATE qr_codes SET is_active = 0"); 
                    $isActive = 1; 
                } else { 
                    $isActive = 0; 
                }
                $pdo->prepare("INSERT INTO qr_codes (display_name, image_path, is_active) VALUES (?, ?, ?)")->execute([$displayName, "uploads/" . $newName, $isActive]);
                $message = "<p style='color: #00e676;'>QR Code deployed!</p>";
            }
        }
    }
}

// 4. Handle Match Deployments with Custom Odds Fields
if (isset($_POST['add_match'])) {
    $tA = trim($_POST['team_a']); 
    $tB = trim($_POST['team_b']); 
    $st = $_POST['status']; 
    $time = $_POST['start_time'];
    $oddsA = floatval($_POST['team_a_odds'] ?? 1.90);
    $oddsB = floatval($_POST['team_b_odds'] ?? 1.90);

    if (!empty($tA) && !empty($tB)) {
        $pdo->prepare("INSERT INTO matches (team_a, team_b, match_status, start_time, team_a_odds, team_b_odds) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$tA, $tB, $st, $time, $oddsA, $oddsB]);
        $message = "<p style='color: #00e676;'>Match with dynamic odds deployed successfully!</p>";
    }
}

// 5. Live Odds Modifier/Update Action
if (isset($_POST['update_odds'])) {
    $mId = intval($_POST['match_id']);
    $oddsA = floatval($_POST['team_a_odds']);
    $oddsB = floatval($_POST['team_b_odds']);
    
    if ($mId > 0) {
        $pdo->prepare("UPDATE matches SET team_a_odds = ?, team_b_odds = ? WHERE id = ?")
            ->execute([$oddsA, $oddsB, $mId]);
        $message = "<p style='color: #00e676;'>Live odds shifted instantly!</p>";
    }
}

// 6. Handle Match Settlements
if (isset($_POST['settle_match'])) {
    $mId = intval($_POST['match_id']); 
    $winner = trim($_POST['winner_team']);
    if ($mId > 0 && !empty($winner)) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE matches SET match_status = 'completed', winner_team = ? WHERE id = ?")->execute([$winner, $mId]);
            
            $wBets = $pdo->prepare("SELECT * FROM bets WHERE match_id = ? AND prediction = ? AND bet_status = 'pending'"); 
            $wBets->execute([$mId, $winner]);
            foreach ($wBets->fetchAll() as $bet) {
                $payout = $bet['stake'] * $bet['odds'];
                $pdo->prepare("UPDATE wallets SET balance = balance + ?, frozen_balance = frozen_balance - ? WHERE user_id = ?")->execute([$payout, $bet['stake'], $bet['user_id']]);
                $pdo->prepare("UPDATE bets SET bet_status = 'won' WHERE id = ?")->execute([$bet['id']]);
                $pdo->prepare("INSERT INTO transactions (user_id, amount, type, status) VALUES (?, ?, 'payout', 'completed')")->execute([$bet['user_id'], $payout]);
            }
            
            $lBets = $pdo->prepare("SELECT * FROM bets WHERE match_id = ? AND prediction != ? AND bet_status = 'pending'"); 
            $lBets->execute([$mId, $winner]);
            foreach ($lBets->fetchAll() as $bet) {
                $pdo->prepare("UPDATE wallets SET frozen_balance = frozen_balance - ? WHERE user_id = ?")->execute([$bet['stake'], $bet['user_id']]);
                $pdo->prepare("UPDATE bets SET bet_status = 'lost' WHERE id = ?")->execute([$bet['id']]);
            }
            $pdo->commit(); 
            $message = "<p style='color: #00e676;'>Match settled completely!</p>";
        } catch (Exception $e) { 
            $pdo->rollBack(); 
            $message = "<p style='color: #ff1744;'>Settle failed.</p>"; 
        }
    }
}

// Data Array Pulls
$pendingWithdrawals = $pdo->query("SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.status = 'pending' AND t.type = 'withdrawal' ORDER BY t.created_at ASC")->fetchAll();
$pendingDeposits = $pdo->query("SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.status = 'pending' AND t.type = 'deposit' ORDER BY t.created_at ASC")->fetchAll();
$activeMatches = $pdo->query("SELECT * FROM matches WHERE match_status != 'completed'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Operations Control Panel</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #1a1a2e; color: #fff; padding: 30px; margin: 0; }
        .admin-box { background: #161623; border: 1px solid #2e2e42; border-radius: 8px; padding: 25px; margin-bottom: 25px; }
        h2 { color: #00e676; margin-top: 0; border-bottom: 1px solid #2e2e42; padding-bottom: 8px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #2e2e42; background: #0f0f1a; color: #fff; border-radius: 4px; box-sizing: border-box; margin-bottom: 15px; }
        button { background: #00e676; color: #1a1a2e; font-weight: bold; border: none; padding: 10px 18px; border-radius: 4px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #2e2e42; }
        th { background: #0f0f1a; color: #00e676; }
        .btn-pay { background: #00e676; color: #1a1a2e; padding: 6px 12px; font-weight: bold; border:none; border-radius:4px; cursor:pointer; margin-right: 5px; }
        .btn-rej { background: #ff1744; color: #fff; padding: 6px 12px; font-weight: bold; border:none; border-radius:4px; cursor:pointer; }
        .inline-form { display: flex; gap: 10px; align-items: center; margin: 0; width: auto; }
        .inline-form input { margin-bottom: 0; width: 80px; padding: 6px; }
    </style>
</head>
<body>
<h1>Admin Operations Console (Hidden Gateway)</h1>
<?php echo $message; ?>

<div class="admin-box">
    <h2 style="color:#ff1744; border-color:#ff1744;">Payout Processing Terminal (Withdrawals)</h2>
    <?php if (empty($pendingWithdrawals)): ?>
        <p style="color: #a0a0a0;">No withdrawal payout request tickets pending execution.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Username</th><th>Payout Amount</th><th>Target Bank Account / Routing Coordinates</th><th>Action Routing</th></tr></thead>
            <tbody>
                <?php foreach ($pendingWithdrawals as $w): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($w['username']); ?></strong></td>
                        <td style="color:#ff1744; font-weight:bold;">-$<?php echo number_format($w['amount'], 2); ?></td>
                        <td><code style="color:#fff; background:#0f0f1a; padding:4px 8px; border-radius:4px;"><?php echo htmlspecialchars($w['utr_number']); ?></code></td>
                        <td>
                            <form action="admin.php" method="POST" style="display:inline;">
                                <input type="hidden" name="tx_id" value="<?php echo $w['id']; ?>">
                                <input type="hidden" name="action_type" value="approve">
                                <button type="submit" name="action_payout" class="btn-pay">Mark Settled (Paid)</button>
                            </form>
                            <form action="admin.php" method="POST" style="display:inline;">
                                <input type="hidden" name="tx_id" value="<?php echo $w['id']; ?>">
                                <input type="hidden" name="action_type" value="reject">
                                <button type="submit" name="action_payout" class="btn-rej">Reject (Refund)</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="admin-box">
    <h2>Incoming User Deposit Approvals</h2>
    <?php if (empty($pendingDeposits)): ?><p style="color:#a0a0a0;">No verification claims pending.</p><?php else: ?>
        <table>
            <thead><tr><th>User</th><th>Amount</th><th>UTR Reference</th><th>Proof</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($pendingDeposits as $d): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($d['username']); ?></strong></td>
                        <td>$<?php echo number_format($d['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($d['utr_number']); ?></td>
                        <td><a href="<?php echo $d['screenshot']; ?>" target="_blank" style="color:#00e676;">View Receipt</a></td>
                        <td>
                            <form action="admin.php" method="POST" style="display:inline;"><input type="hidden" name="tx_id" value="<?php echo $d['id']; ?>"><input type="hidden" name="action_type" value="accept"><button type="submit" name="action_deposit" class="btn-pay">Accept</button></form>
                            <form action="admin.php" method="POST" style="display:inline;"><input type="hidden" name="tx_id" value="<?php echo $d['id']; ?>"><input type="hidden" name="action_type" value="decline"><button type="submit" name="action_deposit" class="btn-rej">Decline</button></form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="admin-box">
    <h2>Deploy New Sports Match</h2>
    <form action="admin.php" method="POST">
        <input type="text" name="team_a" placeholder="Team A Name" required>
        <input type="text" name="team_b" placeholder="Team B Name" required>
        <div style="display: flex; gap: 15px;">
            <input type="number" step="0.01" name="team_a_odds" placeholder="Team A Odds (e.g. 1.85)" value="1.90" required>
            <input type="number" step="0.01" name="team_b_odds" placeholder="Team B Odds (e.g. 2.10)" value="1.90" required>
        </div>
        <select name="status"><option value="live">Live</option><option value="upcoming">Upcoming</option></select>
        <input type="datetime-local" name="start_time" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
        <button type="submit" name="add_match">Deploy Match</button>
    </form>
</div>

<div class="admin-box">
    <h2>Live Match Management Grid (Change Odds / Settle)</h2>
    <?php if (empty($activeMatches)): ?>
        <p style="color:#a0a0a0;">No active fixtures currently open.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Fixture Matchup</th>
                    <th>Adjust Live Market Odds</th>
                    <th>Outright Settlement</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activeMatches as $m): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($m['team_a'] . ' vs ' . $m['team_b']); ?></strong>
                            <br><span style="font-size:12px; color:#00e676;">Status: <?php echo strtoupper($m['match_status']); ?></span>
                        </td>
                        <td>
                            <form action="admin.php" method="POST" class="inline-form">
                                <input type="hidden" name="match_id" value="<?php echo $m['id']; ?>">
                                <label style="font-size:12px; color:#a0a0a0;">A:</label>
                                <input type="number" step="0.01" name="team_a_odds" value="<?php echo $m['team_a_odds']; ?>">
                                <label style="font-size:12px; color:#a0a0a0;">B:</label>
                                <input type="number" step="0.01" name="team_b_odds" value="<?php echo $m['team_b_odds']; ?>">
                                <button type="submit" name="update_odds" style="padding: 6px 10px; background:#00b357;">Shift</button>
                            </form>
                        </td>
                        <td>
                            <form action="admin.php" method="POST" class="inline-form">
                                <input type="hidden" name="match_id" value="<?php echo $m['id']; ?>">
                                <select name="winner_team" style="width:110px; margin-bottom:0; padding:5px;">
                                    <option value="<?php echo htmlspecialchars($m['team_a']); ?>">A Wins</option>
                                    <option value="<?php echo htmlspecialchars($m['team_b']); ?>">B Wins</option>
                                </select>
                                <button type="submit" name="settle_match" style="background:#ff1744; color:#fff; padding:6px 10px;">Settle</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>