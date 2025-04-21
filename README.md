<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Profile</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #fff;
      margin: 0;
      padding: 0;
    }
    .header-bar {
      background-color: #002b5c;
      color: white;
      padding: 10px 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header-bar span {
      font-size: 16px;
    }
    .profile-icon {
      display: flex;
      align-items: center;
    }
    .profile-icon .circle {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background-color: #00a676;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 20px;
      margin-right: 5px;
    }
    .profile-icon .menu {
      width: 20px;
      height: 15px;
      background: linear-gradient(white 3px, transparent 3px) repeat-y;
      background-size: 100% 6px;
    }
    .breadcrumb {
      background-color: #f4f4f4;
      padding: 8px 15px;
      font-size: 14px;
    }
    .container {
      padding: 20px;
      text-align: center;
    }
    .box {
      border: 1px solid #ccc;
      border-radius: 8px;
      width: 90%;
      max-width: 500px;
      margin: 0 auto;
      padding: 20px;
    }
    h2 {
      color: #002b5c;
      font-family: 'Comic Sans MS', cursive;
    }
    .revalidate-btn {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 12px 25px;
      font-size: 16px;
      cursor: pointer;
      margin: 20px 0;
      border-radius: 4px;
    }
    .note {
      color: red;
      font-size: 14px;
    }
    .update-link {
      margin-top: 20px;
      display: block;
      color: blue;
      font-size: 16px;
      text-decoration: none;
    }
    .update-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="header-bar">
    <span>uucms.karnataka.gov.in</span>
    <div class="profile-icon">
      <div class="circle">👤</div>
      <div class="menu"></div>
	  <p class="menu"></p>
	  <a href="menu.html" class="slidebar"</a>
	  </div>
  </div>

  <div class="breadcrumb">
    Home / <strong>Manage Profile</strong>
  </div>

  <div class="container">
  
    <div class="box">
      <h2>Manage Profile</h2>
      <button class="revalidate-btn">Revalidate Aadhaar</button>
      <p class="note">Note: Revalidate Aadhaar only if Name and Phone number change required</p>
      <a href="D:\uucmscr\menu.html" class="update-link">Click here for profile update</a>
    </div>
  </div>

</body>
</html>
