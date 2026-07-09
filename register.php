<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Cricket Betting Platform</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #0c0c14; 
            color: #fff;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
            background-image: linear-gradient(rgba(12, 12, 20, 0.85), rgba(12, 12, 20, 0.95)), url('https://images.unsplash.com/photo-1531415074968-036ba1b575da?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
        }
        
        /* Premium Split Dashboard Layout */
        .page-wrapper {
            display: flex;
            width: 850px;
            background: #161623;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,0.5);
            border: 1px solid #2e2e42;
        }

        /* Left Side: Sports Promo Banner Workspace */
        .promo-banner {
            flex: 1;
            background: linear-gradient(135deg, rgba(0, 230, 118, 0.2), rgba(15, 15, 26, 0.95)), url('https://images.unsplash.com/photo-1624526267942-ab0ff8a3e972?auto=format&fit=crop&w=600&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 40px 30px;
            border-right: 1px solid #2e2e42;
        }

        .promo-banner h1 {
            font-size: 28px;
            color: #00e676;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.6);
            font-weight: 800;
        }

        .promo-banner p {
            color: #e0e0e0;
            font-size: 15px;
            line-height: 1.4;
            margin: 0 0 15px 0;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }

        .badge-live {
            background: #ff1744;
            color: #fff;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
            align-self: flex-start;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        /* Right Side: Registration Form Frame */
        .form-container { 
            width: 380px;
            padding: 40px; 
            background: #11111e;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        h2 { 
            margin: 0 0 6px 0; 
            color: #00e676; 
            font-size: 26px;
            font-weight: 700;
        }
        
        p.subtitle {
            color: #b0b0b0;
            font-size: 14px;
            margin-top: 0;
            margin-bottom: 24px;
        }
        
        label {
            font-size: 12px;
            color: #a0a0a0;
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        input[type="text"], input[type="email"], input[type="password"] { 
            width: 100%; 
            padding: 12px 14px; 
            margin-bottom: 20px; 
            border: 1px solid #2e2e42; 
            border-radius: 6px; 
            box-sizing: border-box; 
            background: #07070f;
            color: #fff;
            font-size: 14px;
            transition: 0.2s;
        }
        
        input:focus {
            border-color: #00e676;
            background: #0f0f1a;
            outline: none;
        }
        
        button { 
            width: 100%; 
            padding: 14px; 
            background: #00e676; 
            border: none; 
            color: #0c0c14; 
            font-weight: bold;
            font-size: 16px; 
            border-radius: 6px; 
            cursor: pointer; 
            transition: 0.2s;
            margin-top: 5px;
        }
        
        button:hover { 
            background: #00b357; 
            transform: translateY(-1px);
        }
        
        .error { 
            background: rgba(255, 23, 68, 0.1);
            color: #ff1744; 
            padding: 12px;
            border-radius: 6px;
            font-size: 14px; 
            margin-bottom: 20px; 
            border: 1px solid rgba(255, 23, 68, 0.2);
            text-align: center;
        }
        
        .success { 
            background: rgba(0, 230, 118, 0.1);
            color: #00e676; 
            padding: 12px;
            border-radius: 6px;
            font-size: 14px; 
            margin-bottom: 20px; 
            border: 1px solid rgba(0, 230, 118, 0.2);
            text-align: center;
        }
        
        .link { 
            text-align: center; 
            margin-top: 25px; 
            font-size: 14px; 
            color: #a0a0a0;
        }
        
        .link a {
            color: #00e676;
            text-decoration: none;
            font-weight: bold;
        }
        
        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="page-wrapper">
    <div class="promo-banner">
        <span class="badge-live">● Live Betting</span>
        <h1>VecCricket Premium Sportsbook</h1>
        <p>Access competitive live match odds, instantaneous settlement modules, and secure withdrawal gates directly on your player profile.</p>
    </div>

    <div class="form-container">
        <h2>Create Account</h2>
        <p class="subtitle">Get your free master betting wallet</p>
        
        <?php 
        if (isset($_SESSION['error'])) {
            echo '<div class="error">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        ?>

        <form action="api/register_action.php" method="POST">
            <label>Username</label>
            <input type="text" name="username" placeholder="e.g. cricketfan99" required>

            <label>Email Address</label>
            <input type="email" name="email" placeholder="name@example.com" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>

            <button type="submit">Register Account</button>
        </form>
        
        <div class="link">
            Already registered? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

</body>
</html>