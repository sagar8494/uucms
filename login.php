<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cricket Betting Platform</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #1a1a2e; 
            color: #fff;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .form-container { 
            background: #161623; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 8px 24px rgba(0,0,0,0.3); 
            width: 340px; 
            border: 1px solid #2e2e42;
        }
        h2 { 
            margin-bottom: 8px; 
            color: #00e676; 
            text-align: center; 
            font-size: 24px;
        }
        p.subtitle {
            text-align: center;
            color: #b0b0b0;
            font-size: 14px;
            margin-top: 0;
            margin-bottom: 24px;
        }
        label {
            font-size: 13px;
            color: #00e676;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 12px; 
            margin-bottom: 18px; 
            border: 1px solid #2e2e42; 
            border-radius: 6px; 
            box-sizing: border-box; 
            background: #0f0f1a;
            color: #fff;
            font-size: 14px;
        }
        input:focus {
            border-color: #00e676;
            outline: none;
        }
        button { 
            width: 100%; 
            padding: 12px; 
            background: #00e676; 
            border: none; 
            color: #1a1a2e; 
            font-weight: bold;
            font-size: 16px; 
            border-radius: 6px; 
            cursor: pointer; 
        }
        button:hover { 
            background: #00b357; 
        }
        .error { 
            background: rgba(255, 23, 68, 0.1);
            color: #ff1744; 
            padding: 10px;
            border-radius: 6px;
            font-size: 14px; 
            margin-bottom: 15px; 
            border: 1px solid rgba(255, 23, 68, 0.2);
            text-align: center;
        }
        .success { 
            background: rgba(0, 230, 118, 0.1);
            color: #00e676; 
            padding: 10px;
            border-radius: 6px;
            font-size: 14px; 
            margin-bottom: 15px; 
            border: 1px solid rgba(0, 230, 118, 0.2);
            text-align: center;
        }
        .link { 
            text-align: center; 
            margin-top: 20px; 
            font-size: 14px; 
            color: #a0a0a0;
        }
        .link a {
            color: #00e676;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Welcome Back</h2>
    <p class="subtitle">Log in to your betting account</p>
    
    <?php 
    if (isset($_SESSION['error'])) {
        echo '<div class="error">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    ?>

    <form action="api/login_action.php" method="POST">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter username" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>

        <button type="submit">Sign In</button>
    </form>
    
    <div class="link">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</div>

</body>
</html>