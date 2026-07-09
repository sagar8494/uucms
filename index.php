<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch the user's wallet balance
$stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
$stmt->execute([$userId]);
$wallet = $stmt->fetch();
$balance = $wallet ? $wallet['balance'] : 0.00;

// SAFE FIX: Keep sorting conditions out to prevent missing column errors
$matchesStmt = $pdo->query("SELECT * FROM matches");
$matches = $matchesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VecCricket - Premium Sportsbook & Casino</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #080810; color: #fff; margin: 0; padding: 0; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #11111e; padding: 15px 40px; border-bottom: 1px solid #222235; }
        .logo { font-size: 24px; font-weight: 800; color: #00e676; text-decoration: none; letter-spacing: 1px; }
        .nav-links { display: flex; gap: 20px; }
        .nav-link { color: #a0a0b5; text-decoration: none; font-weight: bold; font-size: 14px; text-transform: uppercase; }
        .nav-link:hover, .nav-link.active { color: #00e676; }
        
        /* Updated Actions Area Layout for Balance & Transactions */
        .user-actions { display: flex; align-items: center; gap: 12px; }
        .balance-badge { background: #1a1a2e; color: #00e676; padding: 10px 18px; border-radius: 6px; font-weight: 800; text-decoration: none; border: 1px solid rgba(0, 230, 118, 0.2); }
        .action-link { padding: 10px 16px; border-radius: 6px; font-weight: bold; font-size: 13px; text-transform: uppercase; text-decoration: none; transition: 0.2s; }
        .btn-deposit { background: linear-gradient(135deg, #00e676, #00b357); color: #080810; }
        .btn-withdraw { background: #222235; color: #a0a0b5; border: 1px solid #33334d; }
        .btn-withdraw:hover { color: #fff; border-color: #ff1744; }
        
        .main-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: flex; flex-direction: column; gap: 50px; }
        h2.section-title { font-size: 22px; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 20px 0; border-left: 4px solid #00e676; padding-left: 12px; }
        
        /* Casino Lobby Grid Styling */
        .lobby-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 25px; }
        .lobby-card { background: #11111e; border: 1px solid #222235; border-radius: 16px; padding: 30px; display: flex; flex-direction: column; justify-content: space-between; min-height: 180px; transition: transform 0.3s, border-color 0.3s; position: relative; overflow: hidden; }
        .lobby-card:hover { transform: translateY(-5px); border-color: #00e676; }
        .lobby-card::before { content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: radial-gradient(circle, rgba(0,230,118,0.08) 0%, transparent 70%); pointer-events: none; }
        
        .card-meta h3 { margin: 0 0 8px 0; font-size: 24px; font-weight: 800; }
        .card-meta p { color: #8e8e9f; margin: 0; font-size: 14px; line-height: 1.5; }
        
        .play-btn { display: inline-block; background: #1e1e2f; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; text-transform: uppercase; font-size: 13px; margin-top: 20px; border: 1px solid #222235; text-align: center; transition: 0.2s; }
        .lobby-card:hover .play-btn { background: linear-gradient(135deg, #00e676, #00b357); color: #080810; border-color: transparent; }

        /* Sportsbook Fixtures List */
        .matches-list { display: flex; flex-direction: column; gap: 15px; }
        .match-row { background: #11111e; border: 1px solid #222235; border-radius: 12px; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
        .match-teams { font-size: 18px; font-weight: bold; }
        .match-time { color: #a0a0b5; font-size: 13px; margin-top: 4px; }
        .bet-action-btn { background: #00e676; color: #080810; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; font-size: 13px; text-transform: uppercase; }
        .no-matches { background: #11111e; padding: 30px; text-align: center; border-radius: 12px; color: #a0a0b5; border: 1px solid #222235; }
    </style>
</head>
<body>

<div class="navbar">
    <a href="index.php" class="logo">VecCricket</a>
    <div class="nav-links">
        <a href="index.php" class="nav-link active">Sportsbook</a>
        <a href="casino.php" class="nav-link">Live Casino</a>
        <a href="andar_bahar.php" class="nav-link">Andar Bahar</a>
		 <a href="dragon_tiger.php" class="nav-link">Dragon Tiger</a>
    </div>
    <div class="user-actions">
        <span class="balance-badge">Bal: $<?php echo number_format($balance, 2); ?></span>
        <a href="deposit.php" class="action-link btn-deposit">Deposit</a>
        <a href="withdraw.php" class="action-link btn-withdraw">Withdraw</a>
    </div>
</div>

<div class="main-container">
    
    <section>
        <h2 class="section-title">Live Casino Lobby</h2>
        <div class="lobby-grid">
            
            <div class="lobby-card">
                <div class="card-meta">
                    <h3 style="color: #ffea00;">Premium Roulette</h3>
                    <p>Experience standard European layout tracking mechanics. Algorithmic balance security protocols enabled.</p>
                </div>
                <a href="casino.php" class="play-btn">Launch Table</a>
            </div>

            <div class="lobby-card">
                <div class="card-meta">
                    <h3 style="color: #00e676;">Andar Bahar</h3>
                    <p>Traditional fast-paced alternate deal card system. High liquidity payout distributions (1.9x - 2.0x).</p>
                </div>
                <a href="andar_bahar.php" class="play-btn">Join Deal</a>
            </div>
			
            <div class="lobby-card">
    <div class="card-meta">
        <h3 style="color: #ff1744;">Dragon Tiger</h3>
        <p>Ultra-fast single card absolute showdown values. Absolute profit margins with half-stake house protection safety locks.</p>
    </div>
    <a href="dragon_tiger.php" class="play-btn">Open Card Table</a>
</div>

        </div>
    </section>

    <section>
        <h2 class="section-title">Active Sportsbook Matches</h2>
        <div class="matches-list">
            <?php if (count($matches) > 0): ?>
                <?php foreach ($matches as $match): ?>
                    <div class="match-row">
                        <div>
                            <div class="match-teams">
                                <?php 
                                    $teamA = $match['team_a'] ?? $match['teamA'] ?? 'Team A';
                                    $teamB = $match['team_b'] ?? $match['teamB'] ?? 'Team B';
                                    echo htmlspecialchars($teamA) . " vs " . htmlspecialchars($teamB); 
                                ?>
                            </div>
                            <div class="match-time">
                                Scheduled: 
                                <?php 
                                    $time = $match['match_time'] ?? $match['time'] ?? $match['date'] ?? 'Live Now';
                                    echo htmlspecialchars($time); 
                                ?>
                            </div>
                        </div>
                        <a href="place_bet.php?match_id=<?php echo $match['id']; ?>" class="bet-action-btn">Place Wager</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-matches">No active cricket fixtures available at this moment. Explore our Live Casino lobby tables above!</div>
            <?php endif; ?>
        </div>
    </section>

</div>

</body>
</html>