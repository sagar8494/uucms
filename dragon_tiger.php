<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
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
    <title>VecCricket Premium Dragon Tiger</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #080810; color: #fff; margin: 0; padding: 0; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #11111e; padding: 15px 40px; border-bottom: 1px solid #222235; }
        .logo { font-size: 24px; font-weight: 800; color: #00e676; text-decoration: none; letter-spacing: 1px; }
        .nav-links { display: flex; gap: 20px; }
        .nav-link { color: #a0a0b5; text-decoration: none; font-weight: bold; font-size: 14px; text-transform: uppercase; }
        .nav-link:hover, .nav-link.active { color: #00e676; }
        .balance-badge { background: #1a1a2e; color: #00e676; padding: 10px 18px; border-radius: 6px; font-weight: 800; text-decoration: none; border: 1px solid rgba(0, 230, 118, 0.2); }
        
        .table-wrapper { max-width: 1000px; margin: 40px auto; padding: 0 20px; display: flex; flex-direction: column; gap: 30px; }
        
        /* Premium Casino Table Felt Layout */
        .casino-felt { background: radial-gradient(circle, #093044 0%, #03141e 100%); border: 12px solid #3e2723; border-radius: 24px; padding: 50px 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.7), inset 0 0 40px rgba(0,0,0,0.6); display: flex; justify-content: space-around; align-items: center; position: relative; min-height: 25px; }
        
        .bet-zone { display: flex; flex-direction: column; align-items: center; gap: 15px; width: 200px; padding: 20px; border-radius: 12px; border: 2px dashed rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); }
        .zone-title { font-size: 24px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; }
        .color-dragon { color: #ff1744; text-shadow: 0 0 10px rgba(255,23,68,0.4); }
        .color-tiger { color: #29b6f6; text-shadow: 0 0 10px rgba(41,182,246,0.4); }
        .color-tie { color: #ffea00; text-shadow: 0 0 10px rgba(255,234,0,0.4); }
        
        .card-slot { width: 90px; height: 130px; border: 2px solid rgba(255,255,255,0.15); border-radius: 8px; background: rgba(0,0,0,0.4); display: flex; justify-content: center; align-items: center; font-size: 14px; color: #527285; }
        
        /* CSS Card Design */
        .card-asset { width: 85px; height: 125px; background: #fff; border-radius: 6px; color: #000; font-weight: 900; position: relative; display: flex; justify-content: center; align-items: center; font-size: 24px; box-shadow: 0 6px 12px rgba(0,0,0,0.5); animation: deal 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28) forwards; }
        @keyframes deal { from { transform: translateY(-50px) scale(0.5); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }
        .card-asset.suit-red { color: #ff1744; }
        .card-asset .suit-corner { position: absolute; top: 4px; left: 6px; font-size: 14px; font-weight: normal; }
        
        /* Betting Board Controls */
        .controls-panel { background: #11111e; border: 1px solid #222235; border-radius: 16px; padding: 30px; display: flex; gap: 30px; align-items: center; flex-wrap: wrap; }
        .input-group { flex: 1; min-width: 280px; }
        .selection-grid { display: flex; gap: 12px; margin-top: 8px; }
        .select-btn { flex: 1; padding: 16px; border-radius: 8px; font-weight: bold; cursor: pointer; border: 2px solid transparent; text-transform: uppercase; text-align: center; transition: 0.2s; background: #1e1e2f; color: #fff; border-color: #2e2e42; }
        .select-btn.active { border-color: #00e676; background: rgba(0, 230, 118, 0.05); font-weight: 900; box-shadow: 0 0 15px rgba(0,230,118,0.2); }
        
        input[type="number"] { width: 100%; padding: 15px; background: #080810; border: 1px solid #222235; color: #fff; border-radius: 8px; font-size: 16px; font-weight: bold; box-sizing: border-box; margin-top: 8px; }
        .action-btn { background: linear-gradient(135deg, #00e676, #00b357); color: #080810; font-weight: 800; text-transform: uppercase; border: none; padding: 18px 40px; border-radius: 8px; font-size: 16px; cursor: pointer; margin-top: 22px; height: 54px; }
        .action-btn:disabled { background: #222235; color: #52527a; cursor: not-allowed; }
        
        #status-banner { width: 100%; padding: 15px; border-radius: 8px; text-align: center; font-weight: bold; font-size: 16px; display: none; box-sizing: border-box; }
    </style>
</head>
<body>

<div class="navbar">
    <a href="index.php" class="logo">VecCricket</a>
    <div class="nav-links">
        <a href="index.php" class="nav-link">Sportsbook</a>
        <a href="casino.php" class="nav-link">Live Casino</a>
        <a href="andar_bahar.php" class="nav-link">Andar Bahar</a>
        <a href="dragon_tiger.php" class="nav-link active">Dragon Tiger</a>
    </div>
    <div class="user-actions">
        <span class="balance-badge" id="display-balance">Bal: $<?php echo number_format($balance, 2); ?></span>
    </div>
</div>

<div class="table-wrapper">
    <div class="casino-felt">
        <div class="bet-zone">
            <span class="zone-title color-dragon">Dragon</span>
            <div class="card-slot" id="dragon-slot">Waiting...</div>
        </div>

        <div class="bet-zone" style="width: 120px; border: none; background: transparent;">
            <span class="zone-title color-tie" style="font-size: 16px;">Tie Pays 8:1</span>
        </div>

        <div class="bet-zone">
            <span class="zone-title color-tiger">Tiger</span>
            <div class="card-slot" id="tiger-slot">Waiting...</div>
        </div>
    </div>

    <div id="status-banner"></div>

    <div class="controls-panel">
        <div class="input-group">
            <span style="font-size:12px; color:#a0a0b5; font-weight:700; text-transform:uppercase;">Place Target Wager</span>
            <div class="selection-grid">
                <div class="select-btn" onclick="selectSide('dragon', this)">Dragon</div>
                <div class="select-btn" onclick="selectSide('tie', this)">Tie</div>
                <div class="select-btn" onclick="selectSide('tiger', this)">Tiger</div>
            </div>
        </div>
        
        <div class="input-group" style="flex:0.5;">
            <span style="font-size:12px; color:#a0a0b5; font-weight:700; text-transform:uppercase;">Stake Amount ($)</span>
            <input type="number" id="stake-input" min="1" placeholder="0.00">
        </div>
        
        <button type="button" class="action-btn" id="dealBtn" onclick="runDragonTigerWager()">Deal Round</button>
    </div>
</div>

<script>
let selectedSide = "";
const suitSymbols = { 'H': '♥', 'D': '♦', 'C': '♣', 'S': '♠' };

function selectSide(side, element) {
    if(document.getElementById('dealBtn').disabled) return;
    selectedSide = side;
    document.querySelectorAll('.select-btn').forEach(b => b.classList.remove('active'));
    element.classList.add('active');
}

function createCardElement(card) {
    const asset = document.createElement('div');
    const isRed = (card.suit === 'H' || card.suit === 'D');
    asset.className = `card-asset ${isRed ? 'suit-red' : ''}`;
    asset.innerHTML = `
        <span class="suit-corner">${card.rank}${suitSymbols[card.suit]}</span>
        <span>${suitSymbols[card.suit]}</span>
    `;
    return asset;
}

function runDragonTigerWager() {
    const stakeInput = document.getElementById('stake-input');
    const stakeVal = parseFloat(stakeInput.value);
    const dealBtn = document.getElementById('dealBtn');
    const banner = document.getElementById('status-banner');
    
    if(!selectedSide) { alert("Please select a betting zone side."); return; }
    if(isNaN(stakeVal) || stakeVal <= 0) { alert("Please enter a valid stake input."); return; }
    
    dealBtn.disabled = true;
    stakeInput.disabled = true;
    
    // Reset cards layout
    document.getElementById('dragon-slot').innerHTML = "Dealing...";
    document.getElementById('tiger-slot').innerHTML = "Dealing...";
    
    banner.style.display = "block";
    banner.style.background = "rgba(255,255,255,0.03)";
    banner.style.color = "#a0a0b5";
    banner.innerText = "Drawing single card metrics...";

    const formData = new FormData();
    formData.append('prediction', selectedSide);
    formData.append('stake', stakeVal);

    fetch('api/play_dragontiger.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            // 1. Reveal Dragon Card first
            setTimeout(() => {
                const dSlot = document.getElementById('dragon-slot');
                dSlot.innerHTML = "";
                dSlot.appendChild(createCardElement(data.dragon_card));
                
                // 2. Reveal Tiger Card second
                setTimeout(() => {
                    const tSlot = document.getElementById('tiger-slot');
                    tSlot.innerHTML = "";
                    tSlot.appendChild(createCardElement(data.tiger_card));
                    
                    // 3. Print final payout evaluations
                    setTimeout(() => {
                        const isWin = data.message.includes("🎉");
                        const isTieReturn = data.message.includes("👔");
                        
                        if (isWin) {
                            banner.style.background = "rgba(0,230,118,0.1)"; banner.style.color = "#00e676";
                        } else if (isTieReturn) {
                            banner.style.background = "rgba(255,234,0,0.1)"; banner.style.color = "#ffea00";
                        } else {
                            banner.style.background = "rgba(255,23,68,0.1)"; banner.style.color = "#ff1744";
                        }
                        
                        banner.innerText = data.message;
                        document.getElementById('display-balance').innerText = "Bal: $" + parseFloat(data.new_balance).toFixed(2);
                        
                        dealBtn.disabled = false;
                        stakeInput.disabled = false;
                    }, 500);
                    
                }, 800);
            }, 600);
        } else {
            banner.style.background = "rgba(255,23,68,0.1)";
            banner.style.color = "#ff1744";
            banner.innerText = data.message;
            dealBtn.disabled = false;
            stakeInput.disabled = false;
        }
    }).catch(e => {
        banner.innerText = "System link execution failure.";
        dealBtn.disabled = false;
        stakeInput.disabled = false;
    });
}
</script>
</body>
</html>