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
    <title>VecCricket Premium Pro Roulette</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #080810; color: #fff; margin: 0; padding: 0; }
        .navbar { display: flex; justify-content: space-between; align-items: center; background: #11111e; padding: 15px 40px; border-bottom: 1px solid #222235; }
        .logo { font-size: 24px; font-weight: 800; color: #00e676; text-decoration: none; letter-spacing: 1px; }
        .nav-links { display: flex; gap: 20px; }
        .nav-link { color: #a0a0b5; text-decoration: none; font-weight: bold; font-size: 14px; text-transform: uppercase; }
        .nav-link:hover, .nav-link.active { color: #00e676; }
        .balance-badge { background: linear-gradient(135deg, #00e676, #00b357); color: #080810; padding: 10px 20px; border-radius: 6px; font-weight: 800; text-decoration: none; }
        
        .casino-wrapper { display: flex; gap: 40px; padding: 40px; max-width: 1200px; margin: 0 auto; align-items: center; justify-content: center; min-height: 85vh; flex-wrap: wrap; }
        
        .game-panel { flex: 1.2; min-width: 440px; background: #11111e; border-radius: 16px; padding: 40px; border: 1px solid #222235; box-shadow: 0 12px 30px rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; position: relative; height: 480px; }
        
        .roulette-container { position: relative; width: 380px; height: 380px; }
        
        .wheel-outer { width: 100%; height: 100%; background: radial-gradient(circle, #3a220c 0%, #170c04 70%, #000 100%); border-radius: 50%; border: 14px solid #b5893d; box-shadow: 0 0 30px rgba(0,0,0,0.9), inset 0 0 20px #000; display: flex; justify-content: center; align-items: center; transition: transform 5s cubic-bezier(0.1, 0.8, 0.1, 1); transform: rotate(0deg); }
        
        .wheel-numbers-ring { width: 94%; height: 94%; border-radius: 50%; background: #0c0c14; border: 1px solid #b5893d; position: relative; overflow: hidden; display: flex; justify-content: center; align-items: center; }
        
        .wheel-center-cone { width: 90px; height: 90px; border-radius: 50%; background: radial-gradient(circle, #ffe082 0%, #b5893d 60%, #4e342e 100%); box-shadow: 0 6px 15px rgba(0,0,0,0.6); z-index: 10; position: absolute; border: 1px solid #ffe082; }
        
        /* Fixed slice node layout styling utilizing border triangles instead of clip-path masks */
        .number-node { position: absolute; width: 0; height: 0; border-style: solid; border-width: 180px 16px 0 16px; transform-origin: bottom center; bottom: 50%; left: calc(50% - 16px); display: flex; justify-content: center; }
        
        /* Direct absolute text layer wrapper placing numbers clearly at outer bounds */
        .node-text { position: absolute; top: -170px; color: #fff; font-weight: 900; font-size: 13px; transform: rotate(0deg); width: 32px; text-align: center; text-shadow: 0 2px 4px rgba(0,0,0,0.8); z-index: 5; }
        
        .pointer { position: absolute; top: -16px; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 18px solid transparent; border-right: 18px solid transparent; border-top: 32px solid #ffea00; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.7)); z-index: 20; }
        
        .betting-panel { flex: 1; min-width: 350px; background: #11111e; padding: 30px; border-radius: 16px; border: 1px solid #222235; }
        h2 { color: #00e676; margin: 0 0 10px 0; font-size: 26px; font-weight: 800; text-transform: uppercase; }
        p.desc { color: #8e8e9f; font-size: 14px; margin-bottom: 25px; line-height: 1.5; }
        
        .market-label { font-size: 12px; color: #a0a0b5; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; display: block; }
        .selection-grid { display: flex; gap: 15px; margin-bottom: 25px; }
        .select-btn { flex: 1; padding: 18px; border-radius: 8px; cursor: pointer; font-weight: 800; border: 2px solid transparent; font-size: 16px; text-transform: uppercase; text-align: center; transition: 0.2s; }
        .btn-red { background: #ff1744; }
        .btn-black { background: #1e1e2f; border-color: #2e2e42; }
        .select-btn.active { border-color: #00e676; transform: scale(1.04); box-shadow: 0 0 20px rgba(0, 230, 118, 0.4); }
        
        input[type="number"] { width: 100%; padding: 15px; background: #080810; border: 1px solid #222235; color: #fff; border-radius: 8px; font-size: 16px; font-weight: bold; margin-bottom: 25px; }
        .spin-btn { width: 100%; background: linear-gradient(135deg, #00e676, #00b357); color: #080810; border: none; padding: 18px; font-weight: 800; font-size: 16px; border-radius: 8px; cursor: pointer; text-transform: uppercase; }
        .spin-btn:disabled { background: #222235; color: #52527a; cursor: not-allowed; }
        
        #result-display { margin-top: 20px; padding: 16px; border-radius: 8px; text-align: center; font-weight: bold; display: none; border: 1px solid transparent; }
    </style>
</head>
<body>

<div class="navbar">
    <a href="index.php" class="logo">VecCricket</a>
    <div class="nav-links">
        <a href="index.php" class="nav-link">Sportsbook</a>
        <a href="casino.php" class="nav-link active">Live Casino</a>
    </div>
    <div class="user-actions">
        <a href="#" class="balance-badge" id="display-balance">Bal: $<?php echo number_format($balance, 2); ?></a>
    </div>
</div>

<div class="casino-wrapper">
    <div class="game-panel">
        <div class="roulette-container">
            <div class="pointer"></div>
            <div class="wheel-outer" id="wheel">
                <div class="wheel-numbers-ring" id="ring">
                    <div class="wheel-center-cone"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="betting-panel">
        <h2>Premium Roulette</h2>
        <p class="desc">Professional European configuration layout wheel framework.</p>
        
        <form id="casinoForm">
            <span class="market-label">Select Color Structure</span>
            <div class="selection-grid">
                <div class="select-btn btn-red" onclick="pickColor('red', this)">Red</div>
                <div class="select-btn btn-black" onclick="pickColor('black', this)">Black</div>
            </div>
            
            <span class="market-label">Risk Stake Amount ($)</span>
            <input type="number" id="stake-input" name="stake" min="1" placeholder="0.00">
            
            <button type="button" class="spin-btn" id="actionBtn" onclick="executeCasinoWager()">Spin Wheel</button>
        </form>
        
        <div id="result-display"></div>
    </div>
</div>

<script>
let chosenColor = "";
let currentRotation = 0;

const wheelLayout = [0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5, 24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26];
const redNumbers = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];

const ring = document.getElementById('ring');
wheelLayout.forEach((num, index) => {
    const slice = document.createElement('div');
    slice.className = 'number-node';
    
    let colorHex = '#161624'; // Black
    if(num === 0) colorHex = '#00e676'; // Green
    else if(redNumbers.includes(num)) colorHex = '#ff1744'; // Red
    
    // Inject colored slice wedge via border styling
    slice.style.borderColor = `${colorHex} transparent transparent transparent`;
    slice.style.transform = `rotate(${(360 / 37) * index}deg)`;
    
    // Construct text wrapper layer
    const textNode = document.createElement('span');
    textNode.className = 'node-text';
    textNode.innerText = num;
    
    slice.appendChild(textNode);
    ring.appendChild(slice);
});

function pickColor(color, element) {
    if(document.getElementById('actionBtn').disabled) return;
    chosenColor = color;
    document.querySelectorAll('.select-btn').forEach(btn => btn.classList.remove('active'));
    element.classList.add('active');
}

function executeCasinoWager() {
    const stakeInput = document.getElementById('stake-input');
    const stakeVal = parseFloat(stakeInput.value); 
    const resultDiv = document.getElementById('result-display');
    const actionBtn = document.getElementById('actionBtn');
    const wheel = document.getElementById('wheel');
    
    if(!chosenColor) {
        showMsg("Please select Red or Black first!", "error");
        return;
    }
    if(isNaN(stakeVal) || stakeVal <= 0) {
        showMsg("Please enter a valid stake amount.", "error");
        return;
    }
    
    actionBtn.disabled = true;
    stakeInput.disabled = true;
    showMsg("The wheel is in motion...", "pending");

    const formData = new FormData();
    formData.append('outcome', chosenColor);
    formData.append('stake', stakeVal);

    fetch('api/play_casino.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.status === 'success') {
            const totalSlices = 37;
            const targetIndex = data.wheel_index;
            
            const degreesPerSlice = 360 / totalSlices;
            const targetDegrees = (totalSlices - targetIndex) * degreesPerSlice;
            
            const extraSpins = 2160; 
            currentRotation += extraSpins + (targetDegrees - (currentRotation % 360));
            
            wheel.style.transform = `rotate(${currentRotation}deg)`;
            
            setTimeout(() => {
                const isWin = data.message.includes("🎉");
                showMsg(data.message, isWin ? "success" : "loss");
                document.getElementById('display-balance').innerText = "Bal: $" + parseFloat(data.new_balance).toFixed(2);
                
                actionBtn.disabled = false;
                stakeInput.disabled = false;
            }, 5000);
            
        } else {
            showMsg(data.message, "error");
            actionBtn.disabled = false;
            stakeInput.disabled = false;
        }
    }).catch(e => {
        showMsg("Connection error.", "error");
        actionBtn.disabled = false;
        stakeInput.disabled = false;
    });
}

function showMsg(txt, type) {
    const d = document.getElementById('result-display');
    d.style.display = "block";
    d.innerText = txt;
    if(type === 'error' || type === 'loss') {
        d.style.background = "rgba(255, 23, 68, 0.1)"; d.style.color = "#ff1744"; d.style.borderColor = "rgba(255, 23, 68, 0.2)";
    } else if(type === 'success') {
        d.style.background = "rgba(0, 230, 118, 0.1)"; d.style.color = "#00e676"; d.style.borderColor = "rgba(0, 230, 118, 0.2)";
    } else {
        d.style.background = "rgba(255,255,255,0.03)"; d.style.color = "#a0a0b5"; d.style.borderColor = "rgba(255,255,255,0.05)";
    }
}
</script>
</body>
</html>