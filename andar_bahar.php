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
    <title>VecCricket Premium Andar Bahar</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #080810; color: #fff; margin: 0; padding: 0; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #11111e; padding: 15px 40px; border-bottom: 1px solid #222235; }
        .logo { font-size: 24px; font-weight: 800; color: #00e676; text-decoration: none; letter-spacing: 1px; }
        .nav-links { display: flex; gap: 20px; }
        .nav-link { color: #a0a0b5; text-decoration: none; font-weight: bold; font-size: 14px; text-transform: uppercase; }
        .nav-link:hover, .nav-link.active { color: #00e676; }
        .balance-badge { background: linear-gradient(135deg, #00e676, #00b357); color: #080810; padding: 10px 20px; border-radius: 6px; font-weight: 800; text-decoration: none; }
        
        .table-wrapper { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: flex; flex-direction: column; gap: 30px; }
        
        /* Interactive Casino Felt Display Layout */
        .casino-felt { background: radial-gradient(circle, #0e4429 0%, #051d11 100%); border: 12px solid #5d4037; border-radius: 24px; padding: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.7), inset 0 0 40px rgba(0,0,0,0.6); min-height: 400px; display: flex; flex-direction: column; align-items: center; gap: 30px; position: relative; }
        
        .joker-slot { width: 90px; height: 130px; border: 2px dashed rgba(255,255,255,0.2); border-radius: 8px; display: flex; justify-content: center; align-items: center; background: rgba(0,0,0,0.2); position: relative; }
        .section-label { font-size: 11px; text-transform: uppercase; font-weight: 800; color: #a0a0b5; letter-spacing: 1px; position: absolute; top: -22px; width: 100%; text-align: center; }
        
        .deal-board { display: flex; width: 100%; justify-content: space-between; gap: 40px; margin-top: 20px; }
        .side-track { flex: 1; background: rgba(0,0,0,0.15); border-radius: 12px; padding: 20px; min-height: 150px; border: 1px solid rgba(255,255,255,0.05); }
        .track-heading { font-weight: 900; font-size: 18px; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid rgba(255,255,255,0.05); padding-bottom: 8px; display: flex; justify-content: space-between; }
        
        .card-stream { display: flex; flex-wrap: wrap; gap: 10px; }
        
        /* Pro CSS Card Render Layout */
        .card-asset { width: 65px; height: 95px; background: #fff; border-radius: 6px; color: #000; font-weight: 900; position: relative; display: flex; justify-content: center; align-items: center; font-size: 18px; box-shadow: 0 4px 8px rgba(0,0,0,0.3); animation: dealIn 0.25s ease-out forwards; transform: scale(0); }
        @keyframes dealIn { to { transform: scale(1); } }
        .card-asset.suit-red { color: #ff1744; }
        .card-asset .suit-corner { position: absolute; top: 4px; left: 6px; font-size: 11px; font-weight: normal; }
        
        /* Form Control Interface Blocks */
        .controls-panel { background: #11111e; border: 1px solid #222235; border-radius: 16px; padding: 30px; display: flex; gap: 30px; align-items: center; flex-wrap: wrap; }
        .input-group { flex: 1; min-width: 240px; }
        .selection-grid { display: flex; gap: 15px; margin-top: 8px; }
        .select-btn { flex: 1; padding: 16px; border-radius: 8px; font-weight: bold; cursor: pointer; border: 2px solid transparent; text-transform: uppercase; text-align: center; font-size: 15px; transition: 0.2s; user-select: none; }
        .btn-andar { background: #1e1e2f; color: #fff; border-color: #2e2e42; }
        .btn-bahar { background: #1e1e2f; color: #fff; border-color: #2e2e42; }
        .select-btn.active { border-color: #00e676; background: rgba(0, 230, 118, 0.05); font-weight: 900; box-shadow: 0 0 15px rgba(0,230,118,0.2); }
        
        input[type="number"] { width: 100%; padding: 15px; background: #080810; border: 1px solid #222235; color: #fff; border-radius: 8px; font-size: 16px; font-weight: bold; box-sizing: border-box; margin-top: 8px; }
        .action-btn { background: linear-gradient(135deg, #00e676, #00b357); color: #080810; font-weight: 800; text-transform: uppercase; border: none; padding: 18px 40px; border-radius: 8px; font-size: 16px; cursor: pointer; margin-top: 22px; height: 54px; transition: 0.2s; }
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
        <a href="andar_bahar.php" class="nav-link active">Andar Bahar</a>
    </div>
    <div class="user-actions">
        <a href="#" class="balance-badge" id="display-balance">Bal: $<?php echo number_format($balance, 2); ?></a>
    </div>
</div>

<div class="table-wrapper">
    <div class="casino-felt">
        <div class="joker-slot" id="joker-anchor">
            <span class="section-label">Joker Card</span>
        </div>
        
        <div class="deal-board">
            <div class="side-track">
                <div class="track-heading" style="color:#00e676;">Andar <span style="font-size:12px;color:#a0a0b5;">Payout: 1.9x</span></div>
                <div class="card-stream" id="andar-stream"></div>
            </div>
            <div class="side-track">
                <div class="track-heading" style="color:#ff1744;">Bahar <span style="font-size:12px;color:#a0a0b5;">Payout: 2.0x</span></div>
                <div class="card-stream" id="bahar-stream"></div>
            </div>
        </div>
    </div>

    <div id="status-banner"></div>

    <div class="controls-panel">
        <div class="input-group">
            <span style="font-size:12px; color:#a0a0b5; font-weight:700; text-transform:uppercase;">Select Prediction Side</span>
            <div class="selection-grid">
                <div class="select-btn btn-andar" onclick="selectSide('andar', this)">Andar (Inside)</div>
                <div class="select-btn btn-bahar" onclick="selectSide('bahar', this)">Bahar (Outside)</div>
            </div>
        </div>
        
        <div class="input-group" style="flex:0.7;">
            <span style="font-size:12px; color:#a0a0b5; font-weight:700; text-transform:uppercase;">Wager Risk Stake ($)</span>
            <input type="number" id="stake-input" min="1" placeholder="0.00">
        </div>
        
        <button type="button" class="action-btn" id="dealBtn" onclick="runAndarBaharWager()">Place Bet</button>
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

function runAndarBaharWager() {
    const stakeInput = document.getElementById('stake-input');
    const stakeVal = parseFloat(stakeInput.value);
    const dealBtn = document.getElementById('dealBtn');
    const banner = document.getElementById('status-banner');
    
    if(!selectedSide) { alert("Please choose Andar or Bahar side prediction."); return; }
    if(isNaN(stakeVal) || stakeVal <= 0) { alert("Please enter a valid target stake."); return; }
    
    dealBtn.disabled = true;
    stakeInput.disabled = true;
    
    // Wipe old card layout streams clean
    document.getElementById('andar-stream').innerHTML = "";
    document.getElementById('bahar-stream').innerHTML = "";
    const jokerAnchor = document.getElementById('joker-anchor');
    jokerAnchor.innerHTML = '<span class="section-label">Joker Card</span>';
    
    banner.style.display = "block";
    banner.style.background = "rgba(255,255,255,0.03)";
    banner.style.color = "#a0a0b5";
    banner.innerText = "Shuffling deck matrix...";

    const formData = new FormData();
    formData.append('prediction', selectedSide);
    formData.append('stake', stakeVal);

    fetch('api/play_andar_bahar.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            // 1. Reveal center card
            setTimeout(() => {
                jokerAnchor.appendChild(createCardElement(data.joker));
                banner.innerText = "Dealing timeline active...";
                
                // 2. Play dealing sequence simulation
                let totalDelay = 400;
                const maxCards = Math.max(data.andar_sequence.length, data.bahar_sequence.length);
                
                for(let i = 0; i < maxCards; i++) {
                    if(data.andar_sequence[i]) {
                        setTimeout(() => {
                            document.getElementById('andar-stream').appendChild(createCardElement(data.andar_sequence[i]));
                        }, totalDelay);
                        totalDelay += 400;
                    }
                    if(data.bahar_sequence[i]) {
                        setTimeout(() => {
                            document.getElementById('bahar-stream').appendChild(createCardElement(data.bahar_sequence[i]));
                        }, totalDelay);
                        totalDelay += 400;
                    }
                }
                
                // 3. Print final calculation responses
                setTimeout(() => {
                    const isWin = data.message.includes("🎉");
                    banner.style.background = isWin ? "rgba(0,230,118,0.1)" : "rgba(255,23,68,0.1)";
                    banner.style.color = isWin ? "#00e676" : "#ff1744";
                    banner.innerText = data.message;
                    document.getElementById('display-balance').innerText = "Bal: $" + parseFloat(data.new_balance).toFixed(2);
                    
                    dealBtn.disabled = false;
                    stakeInput.disabled = false;
                }, totalDelay);
                
            }, 800);
        } else {
            banner.style.background = "rgba(255,23,68,0.1)";
            banner.style.color = "#ff1744";
            banner.innerText = data.message;
            dealBtn.disabled = false;
            stakeInput.disabled = false;
        }
    }).catch(e => {
        banner.innerText = "Processing link failure.";
        dealBtn.disabled = false;
        stakeInput.disabled = false;
    });
}
</script>
</body>
</html>