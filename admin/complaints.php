<?php
session_start();
require_once "../cons/config.php"; // include your database connection file

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Protect admin dashboard
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../login.php");
    exit;
}

// Fetch complaints from database
$active_complaints = [];
$archived_complaints = [];

$sql = "SELECT c.complaint_id, c.user_id, c.complaint_title, c.date_filed, c.status, c.tracking_number, u.fullname 
        FROM complaints c
        JOIN users u ON c.user_id = u.user_id
        ORDER BY c.date_filed DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (in_array(strtolower($row['status']), ['pending', 'ongoing'])) {
            $active_complaints[] = $row;
        } else {
            $archived_complaints[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Complaints</title>
  <link rel="icon" href="../assets/images/ghost.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    body { background:#FFF8E1; min-height: 100vh; display: flex; }

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
      color: #ffffff;
      margin-bottom: 15px;
    }

    .sidebar img {
      display: block;
      margin: 0 auto 20px;
      width: 100px;
      height: 100px;
      border-radius: 50%;
      border: 2px solid rgb(225, 234, 39);
      background: rgba(255, 255, 255, 0.1);
      padding: 5px;
      object-fit: cover;
    }

    .sidebar ul { list-style: none; }
    .sidebar ul li { margin: 10px 0; }

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

    .sidebar ul li a i { margin-right: 10px; }
    .sidebar ul li a:hover, .sidebar ul li a.active { background: #4a90e2; color: #fff; }

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

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(255,255,255,0.2);
    }

    .header h1 { font-size: 22px; color: #fff; }

    .header .right-section {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .notification { position: relative; cursor: pointer; }
    .notification i { font-size: 20px; color: #fff; }

    .notification .badge {
      position: absolute;
      top: -5px;
      right: -8px;
      background: rgb(213, 93, 46);
      color: white;
      font-size: 12px;
      font-weight: bold;
      padding: 2px 6px;
      border-radius: 50%;
    }

    .header .user { display: flex; align-items: center; gap: 10px; }
    .header .user i { font-size: 20px; color: #fff; }

    .search-bar {
      margin: 20px 0;
      display: flex;
      justify-content: flex-end;
    }

    .search-bar input {
      padding: 8px 12px;
      border-radius: 6px 0 0 6px;
      border: none;
      outline: none;
      width: 250px;
    }

    .search-bar button {
      padding: 8px 12px;
      border: none;
      background: #4a90e2;
      color: #fff;
      border-radius: 0 6px 6px 0;
      cursor: pointer;
    }

    .table-container {
      background: rgba(255,255,255,0.08);
      padding: 20px;
      border-radius: 10px;
      overflow-x: auto;
      margin-bottom: 30px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      color: #fff;
    }

    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid rgba(255,255,255,0.2);
      text-align: left;
      font-size: 14px;
    }

    th { background: rgba(74,144,226,0.6); font-weight: bold; }
    tr:hover { background: rgba(255,255,255,0.1); }

    .action-btn {
      padding: 6px 10px;
      margin: 2px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 12px;
    }

    .resolve { background: #2ECC71; color: white; }
    .dismiss { background: #E74C3C; color: white; }
    .view { background: #3498DB; color: white; }
    .edit { background: #F39C12; color: white; }

    .status {
      padding: 4px 8px;
      border-radius: 6px;
      font-weight: bold;
      font-size: 12px;
      display: inline-block;
      text-align: center;
      min-width: 80px;
    }

    .pending { background: #F1C40F; color: #000; }
    .ongoing { background: #3498DB; color: #fff; }
    .resolved { background: #2ECC71; color: #fff; }
    .dismissed { background: #E74C3C; color: #fff; }

    h2.section-title { margin: 15px 0; font-size: 18px; color: #FFD54F; }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Barangay Connect</h2><br>
    <img src="../assets/images/bg_logo.png">
    <ul>
      <li><a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
      <li><a href="document_request.php"><i class="fa-solid fa-file-lines"></i> Document Requests</a></li>
      <li><a href="complaints.php" class="active"> <i class="fa-solid fa-comments"></i> Complaints</a></li>
      <li><a href="residents.php"><i class="fa-solid fa-users"></i> Residents</a></li>
      <li><a href="officials.php"><i class="fa-solid fa-user-shield"></i> Officials</a></li>
      <li><a href="sms_history.php"><i class="fa-solid fa-message"></i> SMS History</a></li>
      <li><a href="activity_logs.php"><i class="fa-solid fa-list-check"></i> Activity Logs</a></li>
      <li><a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
      <li><a href="landing_page.php"><i class="fa-solid fa-house"></i> Landing Page View</a></li>
      <li><a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');">
        <i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="header">
      <h1>Admin Dashboard</h1>
      <div class="right-section">
        <div class="notification">
          <i class="fa-solid fa-bell"></i>
          <span class="badge" id="notifCount">0</span>
        </div>
        <div class="user">
          <i class="fa-solid fa-user-circle"></i>
          <span>
            <?php 
              echo isset($_SESSION["fullname"], $_SESSION["role"]) 
                ? htmlspecialchars($_SESSION["fullname"]) . " / " . htmlspecialchars($_SESSION["role"]) 
                : "Guest";
            ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search complaints...">
      <button><i class="fa fa-search"></i></button>
    </div>

    <!-- Active Complaints -->
    <h2 class="section-title">Active Complaints</h2>
    <div class="table-container">
      <table id="complaintsTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Resident Name</th>
            <th>Complaint</th>
            <th>Tracking No.</th>
            <th>Date Filed</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($active_complaints)): ?>
            <?php foreach ($active_complaints as $index => $row): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($row['fullname']) ?></td>
                <td><?= htmlspecialchars($row['complaint_title']) ?></td>
                <td><?= htmlspecialchars($row['tracking_number']) ?></td>
                <td><?= htmlspecialchars($row['date_filed']) ?></td>
                <td>
                  <span class="status <?= strtolower($row['status']) ?>">
                    <?= ucfirst($row['status']) ?>
                  </span>
                </td>
                <td>
                  <button class="action-btn resolve">Resolve</button>
                  <button class="action-btn dismiss">Dismiss</button>
                  <button class="action-btn edit">Edit</button>
                  <button class="action-btn view">View</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" style="text-align:center;">No active complaints found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Archived Complaints -->
    <h2 class="section-title">Archived Complaints</h2>
    <div class="table-container">
      <table id="archivedTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Resident Name</th>
            <th>Complaint</th>
            <th>Tracking No.</th>
            <th>Date Filed</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($archived_complaints)): ?>
            <?php foreach ($archived_complaints as $index => $row): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($row['fullname']) ?></td>
                <td><?= htmlspecialchars($row['complaint_title']) ?></td>
                <td><?= htmlspecialchars($row['tracking_number']) ?></td>
                <td><?= htmlspecialchars($row['date_filed']) ?></td>
                <td><span class="status <?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No archived complaints found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Search filter
    document.getElementById("searchInput").addEventListener("keyup", function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll("#complaintsTable tbody tr, #archivedTable tbody tr");
      rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
      });
    });

    // Notification count
    const notifBadge = document.getElementById("notifCount");
    function updateNotifCount() {
      let active = document.querySelectorAll("#complaintsTable .status.pending, #complaintsTable .status.ongoing").length;
      notifBadge.textContent = active;
    }
    updateNotifCount();
  </script>
</body>
</html>
