<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Profile</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background: #fff;
    }
    .navbar {
      background-color: #003366;
      color: white;
      padding: 10px 15px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .navbar a {
      color: white;
      text-decoration: none;
      font-weight: bold;
    }
    .container {
      max-width: 500px;
      margin: 40px auto;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      text-align: center;
    }
    h2 {
      font-family: cursive;
      color: #003366;
      margin-bottom: 20px;
    }
    .btn {
      background-color: #007bff;
      border: none;
      padding: 10px 20px;
      color: white;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
    }
    .note {
      margin-top: 10px;
      color: red;
      font-size: 12px;
    }
    .link {
      margin-top: 15px;
      display: block;
      color: blue;
      text-decoration: none;
    }
    @media (max-width: 600px) {
      .container {
        margin: 20px;
        padding: 15px;
      }
    }
  </style>
</head>
<body>

  <div class="navbar">
    <span>uucms</span>
    <a href="menu.html">&#9776; Menu</a>
  </div>

  <div class="container">
    <h2>Manage Profile</h2>
    <button class="btn">Revalidate Aadhaar</button>
    <p class="note">Note: Revalidate Aadhaar only if Name and Phone number change required</p>
    <a href="#" class="link">Click here for profile update</a>
  </div>

</body>
</html>
