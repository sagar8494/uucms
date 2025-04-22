<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Profile</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
    }
    .header {
      background-color: #003366;
      color: white;
      padding: 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .menu-btn {
      font-size: 24px;
      cursor: pointer;
    }
    .sidebar {
      height: 100%;
      width: 0;
      position: fixed;
      z-index: 1;
      top: 0;
      left: 0;
      background-color: #0d2c3d;
      overflow-x: hidden;
      transition: 0.3s;
      padding-top: 60px;
    }
    .sidebar a {
      padding: 10px 20px;
      text-decoration: none;
      font-size: 18px;
      color: white;
      display: block;
    }
    .sidebar a:hover {
      background-color: #1a4d66;
    }
    .main-content {
      padding: 20px;
    }
    .btn {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 8px 16px;
      cursor: pointer;
      border-radius: 4px;
    }
    .note {
      color: red;
      font-size: 12px;
    }
  </style>
</head>
<body>

  <div class="header">
    <span class="menu-btn" onclick="toggleSidebar()">&#9776;</span>
    <span>uucms.karnataka.gov.in</span>
  </div>

  <div id="mySidebar" class="sidebar">
    <a href="#">Home</a>
    <a href="#">Student</a>
    <a href="#">Academics</a>
    <a href="#">Exam</a>
    <a href="#">Reports</a>
    <a href="#">Help Desk</a>
  </div>

  <div class="main-content">
    <h2>Manage Profile</h2>
    <button class="btn">Revalidate Aadhaar</button>
    <p class="note">Note: Revalidate Aadhaar only if Name and Phone number change required</p>
    <br><br>
    <a href="#" style="color: blue;">Click here for profile update</a>
  </div>

  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById("mySidebar");
      sidebar.style.width = sidebar.style.width === "250px" ? "0" : "250px";
    }
  </script>

</body>
</html>
