<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Admin Dashboard</title>
  <link rel="icon" href="../assets/images/ghost.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background:#FFF8E1;
      min-height: 100vh;
      display: flex;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background:#343A40;
      color: #fff;
      padding: 20px;
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      box-shadow: 2px 0 10px rgba(245, 245, 245, 0.94);
    }

   sidebar.h2 {
  text-align: center;
  font-size: 24px;
  color: #ffffff;
  margin-bottom: 15px;
}

img {
      display: block;
      margin: 0 auto 20px;
      max-width: 120px;
      height: auto;
      border-radius: 50%;
      border: 2px solid rgb(225, 234, 39);
      background: rgba(255,255,255,0.1);
      padding: 5px;
    }


    .sidebar ul {
      list-style: none;
    }

    .sidebar ul li {
      margin: 15px 0;
    }

    .sidebar ul li a {
      color: #ddd;
      text-decoration: none;
      font-size: 16px;
      display: flex;
      align-items: center;
      padding: 10px;
      border-radius: 6px;
      transition: 0.3s;
    }

    .sidebar ul li a i {
      margin-right: 10px;
    }

    .sidebar ul li a:hover {
      background: #4a90e2;
      color: #fff;
    }

    /* Main content */
    .main-content {
      margin-left: 250px;
      flex: 1;
      display: flex;
      flex-direction: column;
      background: rgba(0, 0, 0, 0.55);
      color: #fff;
      padding: 20px;
    }

    /* Header */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(255,255,255,0.2);
    }

    .header h1 {
      font-size: 22px;
      color: #fff;
    }
    .header .right-section {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    /* Notification */
    .notification {
      position: relative;
      cursor: pointer;
    }

    .notification i {
      font-size: 20px;
      color:rgb(242, 245, 248);
    }

    .notification .badge {
      position: absolute;
      top: -5px;
      right: -8px;
      background:rgb(213, 93, 46);
      color: white;
      font-size: 12px;
      font-weight: bold;
      padding: 2px 6px;
      border-radius: 50%;
    }

    /* User */
    .header .user {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .header .user i {
      font-size: 20px;
      color:rgb(233, 237, 241);
    }


    /* Dashboard cards */
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .card {
      background: rgba(255,255,255,0.1);
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      transition: 0.3s;
    }

    .card:hover {
      background: rgba(74, 144, 226, 0.4);
    }

    .card h3 {
      margin-bottom: 10px;
      font-size: 18px;
    }

    .card p {
      font-size: 14px;
      color: #ddd;
    }
  </style>
</head> 
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Barangay Connect</h2><br>
    <img src="../assets/images/bg_logo.png">
    <ul>
      <li><a href="admin_dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
      <li><a href="document_request.php"><i class="fa-solid fa-file-lines"></i> Document Requests</a></li>
      <li><a href="complaints.php"><i class="fa-solid fa-comments"></i> Complaints</a></li>
      <li><a href="residents.php"><i class="fa-solid fa-users"></i> Residents</a></li>
      <li><a href="officials.php"><i class="fa-solid fa-user-shield"></i> Officials</a></li>
      <li><a href="sms_history.php"><i class="fa-solid fa-message"></i> SMS History</a></li>
      <li><a href="activity_logs.php" class="active"><i class="fa-solid fa-list-check"></i> Activity Logs</a></li>
      <li><a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
      <li><a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');">
    <i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>

    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Header -->
    <div class="header">
      <h1>Admin Dashboard</h1>
      <div class="right-section">
        <div class="notification">
          <i class="fa-solid fa-bell"></i>
          <span class="badge">#</span> <!-- Dynamic badge count -->
        </div>
      <div class="user">
        <i class="fa-solid fa-user-circle"></i>
        <span>Admin</span>
      </div>
    </div>
  </div>

    <!-- Dashboard Cards -->
    <div class="cards">
      <div class="card">
        <h3>#</h3>
        <p>Total Complaints</p>
      </div>
      <div class="card">
        <h3>#</h3>
        <p>Document Requests</p>
      </div>
      <div class="card">
        <h3>#</h3>
        <p>Barangay Officials</p>
      </div>
      <div class="card">
        <h3>#</h3>
        <p>Registered Residents</p>
      </div>
    </div>
  </div>

</body>
</html>
