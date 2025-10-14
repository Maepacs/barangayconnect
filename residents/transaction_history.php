<?php
session_start();
require_once "../cons/config.php"; // Make sure this defines $conn

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Protect resident dashboard
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Resident") {
    header("Location: ../login.php");
    exit;
}

// Get user_id from session
$user_id = $_SESSION["user_id"];

// Fetch fullname and role from users table
$stmt = $conn->prepare("SELECT fullname, role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($fullname, $role);
$stmt->fetch();
$stmt->close();

// Optional: store in session for reuse
$_SESSION["role"] = $role;
$_SESSION["fullname"] = $fullname;

// Escape output for safety
$role = htmlspecialchars($role);
$fullname = htmlspecialchars($fullname);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Transaction History</title>
  <link rel="icon" href="../assets/images/ghost.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Global Styles */
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
}

/* Sidebar */
.sidebar {
    width: 250px;
    background: #343A40;
    color: #fff;
    padding: 20px;
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    box-shadow: 2px 0 10px rgba(245, 245, 245, 0.94);
}

.sidebar h2 {
    text-align: center;
    font-size: 24px;
    color: #fff;
    margin-bottom: 15px;
}

.sidebar img {
    display: block;
    margin: 0 auto 20px;
    max-width: 120px;
    border-radius: 50%;
    border: 2px solid rgb(225, 234, 39);
    background: rgba(255, 255, 255, 0.1);
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

.sidebar ul li a:hover,
.sidebar ul li a.active {
    background: #4a90e2;
    color: #fff;
}

/* Main Content */
.main-content {
    position: fixed;
    top: 0;
    left: 250px;       /* since you have sidebar = 250px */
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    background:rgba(52, 58, 64, 0.68);
    color: #fff;
    padding: 20px;
    overflow-y: auto;  /* enable scrolling inside */
}

/* Header */
.header {
    position: sticky;     /* stays at top when scrolling */
    top: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    z-index: 10;          /* make sure it's above content */
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

.notification {
    position: relative;
    cursor: pointer;
}

.notification i {
    font-size: 20px;
    color: rgb(242, 245, 248);
}

.notification .badge {
    position: absolute;
    top: -5px;
    right: -8px;
    background: rgb(213, 93, 46);
    color: #fff;
    font-size: 12px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 50%;
}

.header .user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.header .user i {
    font-size: 20px;
    color: rgb(233, 237, 241);
}

/* Transaction Table */
.transaction-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    overflow: hidden;
}

.transaction-table th,
.transaction-table td {
    padding: 12px 15px;
    text-align: left;
}

.transaction-table th {
    background: #4a90e2;
    color: #fff;
}

.transaction-table tr:nth-child(even) {
    background: rgba(255, 255, 255, 0.05);
}

.transaction-table tr:hover {
    background: rgba(255, 255, 255, 0.2);
    cursor: pointer;
}

.status {
    padding: 5px 10px;
    border-radius: 6px;
    font-weight: bold;
    text-align: center;
}

.status.pending {
    background: #f39c12;
    color: #fff;
}

.status.completed {
    background: #27ae60;
    color: #fff;
}

.status.cancelled {
    background: #c0392b;
    color: #fff;
}
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Barangay Connect</h2><br>
  <img src="../assets/images/bg_logo.png">
  <ul>
    <li><a href="user_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
    <li><a href="file_complaint.php"><i class="fa-solid fa-plus"></i> File a Complaint</a></li>
    <li><a href="request_document.php"><i class="fa-solid fa-file-circle-plus"></i> Request a Document</a></li>
    <li><a href="transaction_history.php" class="active"><i class="fa-solid fa-receipt"></i> Transaction History</a></li>
    <li><a href="user_sms.php"><i class="fa-solid fa-sms"></i> SMS History</a></li>
    <li><a href="account_settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
    <li><a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="header">
    <h1>Transaction History</h1>
    <div class="right-section">
      <div class="notification">
        <i class="fa-solid fa-bell"></i>
        <span class="badge">3</span>
      </div>
      <div class="user">
      <i class="fa-solid fa-user-circle"></i>
  <span>
    <?php 
      echo isset($_SESSION["fullname"]) ? $_SESSION["fullname"] . " / " . $_SESSION["role"] : "Guest"; 
    ?>
  </span>
</div>
      </div>
    </div> <!-- ✅ Closed header properly -->


  <!-- Transaction Table -->
  <table class="transaction-table">
    <thead>
      <tr>
        <th>Date</th>
        <th>Document</th>
        <th>Purpose</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>2025-09-20</td>
        <td>Barangay Clearance</td>
        <td>Employment</td>
        <td><span class="status completed">Completed</span></td>
      </tr>
      <tr>
        <td>2025-09-22</td>
        <td>Certificate of Residency</td>
        <td>School Requirement</td>
        <td><span class="status pending">Pending</span></td>
      </tr>
      <tr>
        <td>2025-09-23</td>
        <td>Indigency Certificate</td>
        <td>Scholarship</td>
        <td><span class="status cancelled">Cancelled</span></td>
      </tr>
      <!-- Add more rows dynamically from database -->
    </tbody>
  </table>
</div>

</body>
</html>
