<?php
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Protect admin dashboard
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    // If not logged in or not an admin, redirect to login
    header("Location: ../login.php"); // go up one folder
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Settings</title>
  <link rel="icon" href="../assets/images/BG_logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
  background: #FFF8E1;
  min-height: 100vh;
  display: flex;
  color: #fff;
}

 /* Sidebar */
.sidebar {
  width: 260px;
  background: #343A40;
  color: #fff;
  padding: 20px;
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  box-shadow: 2px 0 10px rgba(245, 245, 245, 0.94);
  
  /* Added for scrolling */
  overflow-y: auto;     /* Enable vertical scroll */
  overflow-x: hidden;   /* Hide horizontal scroll */
  scrollbar-width: thin; /* For Firefox */
  scrollbar-color: #555 #343A40; /* Thumb and track color */
}

   sidebar.h2 {
  text-align: center;
  font-size: 24px;
  color: #ffffff;
  margin-bottom: 15px;
}

.sidebar img {
  display: block;
  margin: 0 auto 20px;
  width: 100px;      /* adjust width */
  height: 100px;     /* same as width for a circle */
  border-radius: 50%;
  border: 2px solid rgb(225, 234, 39);
  background: rgba(255, 255, 255, 0.1);
  padding: 5px;
  object-fit: cover; /* ✅ keeps image from stretching */
}



    .sidebar ul {
      list-style: none;
    }

    .sidebar ul li {
      margin: 10px 0;
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

    /* Hover effect */
.sidebar ul li a:hover,
.sidebar ul li a.active {
  background: #4a90e2;
  color: #fff;
}

/* Main Content */
.main-content {
  margin-left: 250px;
  flex: 1;
  display: flex;
  flex-direction: column;
  background: rgba(0, 0, 0, 0.55);
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
}

.header .right-section {
  display: flex;
  align-items: center;
  gap: 20px;
}

.header .user {
  display: flex;
  align-items: center;
  gap: 10px;
}

.header .user i {
  font-size: 20px;
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

/* Form */
.settings-form {
  margin-top: 30px;
  background: rgba(255,255,255,0.08);
  padding: 25px;
  border-radius: 10px;
  max-width: 600px;
}

.settings-form h2 {
  color: #FFD54F;
  margin-bottom: 20px;
}

.settings-form label {
  display: block;
  margin-bottom: 6px;
  font-weight: bold;
  color: #fff;
}

.settings-form input[type="text"],
.settings-form input[type="email"],
.settings-form input[type="password"],
.settings-form select {
  width: 100%;
  padding: 10px 12px;
  margin-bottom: 20px;
  border: none;
  border-radius: 6px;
  outline: none;
  background: rgba(255,255,255,0.1);
  color: #fff;
}

.settings-form input::placeholder {
  color: #ccc;
}

.settings-form button {
  padding: 10px 20px;
  background: #4a90e2;
  border: none;
  color: #fff;
  font-weight: bold;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.3s;
}

.settings-form button:hover {
  background: #357ABD;
}
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Barangay Connect</h2><br>
    <img src="../assets/images/bg_logo.png">
    <ul>
      <li><a href="admin_dashboard.php" ><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
      <li><a href="document_request.php"><i class="fa-solid fa-file-lines"></i> Document Requests</a></li>
      <li><a href="complaints.php"><i class="fa-solid fa-comments"></i> Complaints</a></li>
      <li><a href="residents.php"><i class="fa-solid fa-users"></i> Residents</a></li>
      <li><a href="household.php"><i class="fa-solid fa-people-roof"></i> Household Records</a></li>
      <li><a href="officials.php"><i class="fa-solid fa-user-shield"></i> Officials</a></li>
      <li><a href="sms_history.php"><i class="fa-solid fa-message"></i> SMS History</a></li>
      <li><a href="activity_logs.php"><i class="fa-solid fa-list-check"></i> Activity Logs</a></li>
      <li><a href="settings.php" class="active"><i class="fa-solid fa-gear"></i> Settings</a></li>
      <li><a href="landing_page.php"><i class="fa-solid fa-house"></i> Landing Page View</a></li>
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
          <!-- ✅ Use consistent session variable -->
          <span>  <?php 
             if(isset($_SESSION["fullname"], $_SESSION["role"])) {
              echo htmlspecialchars($_SESSION["fullname"]) . " / " . htmlspecialchars($_SESSION["role"]);
          } else {
              echo "Guest";
          }
          
            ?></span>
        </div>
      </div>
    </div>

    <!-- Settings Form -->
    <form class="settings-form">
      <h2>Account Settings</h2>

      <label for="username">Username</label>
      <input type="text" id="username" placeholder="Enter your username">

      <label for="email">Email</label>
      <input type="email" id="email" placeholder="Enter your email">

      <label for="password">Password</label>
      <input type="password" id="password" placeholder="Enter new password">

      <label for="notifications">Notifications</label>
      <select id="notifications">
        <option value="enabled">Enabled</option>
        <option value="disabled">Disabled</option>
      </select>

      <button type="submit">Save Changes</button>
    </form>
  </div>
</body>

<script>
if (performance.navigation.type === 2) {  
    // Back/forward navigation
    window.location.href = "../login.php"; // ← go up one folder
}
</script>

</html>
