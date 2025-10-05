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
  <title>Barangay Connect | Document Requests</title>
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
  background: #FFF8E1;
  min-height: 100vh;
  display: flex;
  color: #fff;
}

/* -------------------
   Sidebar
-------------------- */
    .sidebar {
  width: 250px;
  background: #343A40;
  color: #fff;
  padding: 20px;
  position: fixed;   /* ✅ keep sidebar fixed on screen */
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

/* -------------------
   Main Content
-------------------- */
.main-content {
  margin-left: 250px;
  flex: 1;
  display: flex;
  flex-direction: column;
  background: rgba(0, 0, 0, 0.55);
  padding: 20px;
}

/* -------------------
   Header
-------------------- */
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

/* -------------------
   Notifications
-------------------- */
.notification {
  position: relative;
  cursor: pointer;
}

.notification i {
  font-size: 20px;
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

/* -------------------
   Search Bar
-------------------- */
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

/* -------------------
   Table
-------------------- */
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
}

th, td {
  padding: 12px 15px;
  border-bottom: 1px solid rgba(255,255,255,0.2);
  text-align: left;
  font-size: 14px;
}

th {
  background: rgba(74,144,226,0.6);
  font-weight: bold;
}

tr:hover {
  background: rgba(255,255,255,0.1);
}

/* -------------------
   Buttons
-------------------- */
.action-btn {
  padding: 6px 10px;
  margin: 2px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
}

.action-btn.view     { background: #3498DB; color: #fff; }
.action-btn.edit     { background: #F39C12; color: #fff; }
.action-btn.approve  { background: #2ECC71; color: #fff; }
.action-btn.decline  { background: #E74C3C; color: #fff; }

/* -------------------
   Status Labels
-------------------- */
.status {
  padding: 4px 8px;
  border-radius: 6px;
  font-weight: bold;
  font-size: 12px;
  display: inline-block;
  text-align: center;
  min-width: 80px;
}

.status.pending    { background: #F1C40F; color: #000; }
.status.processing { background: #3498DB; color: #fff; }
.status.approved   { background: #2ECC71; color: #fff; }
.status.declined   { background: #E74C3C; color: #fff; }

/* -------------------
   Section Titles
-------------------- */
.section-title {
  margin: 15px 0;
  font-size: 18px;
  color: #FFD54F;
}

/* -------------------
   Dropdown (Edit Status)
-------------------- */
.status-dropdown {
  padding: 6px 8px;
  border-radius: 6px;
  border: 1px solid #4a90e2;
  background: #fff;
  font-size: 13px;
  color: #333;
  outline: none;
  transition: 0.3s;
}

.status-dropdown:focus {
  border-color: #2ECC71;
  box-shadow: 0 0 6px rgba(46, 204, 113, 0.5);
}

.status-dropdown option {
  padding: 8px;
}

  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Barangay Connect</h2><br>
    <img src="../assets/images/bg_logo.png">
    <ul>
      <li><a href="admin_dashboard.php"> <i class="fa-solid fa-gauge"></i> Dashboard</a></li>
      <li><a href="document_request.php" class="active"><i class="fa-solid fa-file-lines"></i> Document Requests</a></li>
      <li><a href="complaints.php"><i class="fa-solid fa-comments"></i> Complaints</a></li>
      <li><a href="residents.php"><i class="fa-solid fa-users"></i> Residents</a></li>
      <li><a href="officials.php"><i class="fa-solid fa-user-shield"></i> Officials</a></li>
      <li><a href="sms_history.php"><i class="fa-solid fa-message"></i> SMS History</a></li>
      <li><a href="activity_logs.php"> <i class="fa-solid fa-list-check"></i> Activity Logs</a></li>
      <li><a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
      <li><a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');">
        <i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      </li>
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


    <!-- Search Bar -->
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search document requests...">
      <button><i class="fa fa-search"></i></button>
    </div>

    <!-- Active Requests -->
    <h2 class="section-title">Active Requests</h2>
    <div class="table-container">
      <table id="requestsTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Resident Name</th>
            <th>Document Type</th>
            <th>Date Filed</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td></td>
            <td>Pedro Reyes</td>
            <td>Barangay Clearance</td>
            <td>2025-09-25</td>
            <td><span class="status pending">Pending</span></td>
            <td>
              <button class="action-btn approve">Approve</button>
              <button class="action-btn decline">Decline</button>
              <button class="action-btn edit">Edit</button>
              <button class="action-btn view">View</button>
            </td>
          </tr>
          <tr>
            <td></td>
            <td>Ana Lopez</td>
            <td>Business Permit</td>
            <td>2025-09-24</td>
            <td><span class="status processing">Processing</span></td>
            <td>
              <button class="action-btn approve">Approve</button>
              <button class="action-btn decline">Decline</button>
              <button class="action-btn edit">Edit</button>
              <button class="action-btn view">View</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Archived Requests -->
    <h2 class="section-title">Archived Requests</h2>
    <div class="table-container">
      <table id="archivedTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Resident Name</th>
            <th>Document Type</th>
            <th>Date Filed</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td></td>
            <td>Juan Dela Cruz</td>
            <td>Barangay ID</td>
            <td>2025-09-20</td>
            <td><span class="status approved">Approved</span></td>
          </tr>
          <tr>
            <td></td>
            <td>Maria Santos</td>
            <td>Residency Certificate</td>
            <td>2025-09-18</td>
            <td><span class="status declined">Declined</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    const notifBadge = document.getElementById("notifCount");

    function updateNotifCount() {
      let active = document.querySelectorAll("#requestsTable .status.pending, #requestsTable .status.processing");
      notifBadge.textContent = active.length;
    }

    function assignRequestIDs() {
      let allRows = document.querySelectorAll("#requestsTable tbody tr, #archivedTable tbody tr");
      allRows.forEach((row, index) => {
        let idCell = row.querySelector("td:first-child");
        if (!idCell.dataset.fixed) {
          idCell.textContent = "D" + (index + 1);
          idCell.dataset.fixed = "true";
        }
      });
    }

    updateNotifCount();
    assignRequestIDs();

    document.getElementById("searchInput").addEventListener("keyup", function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll("#requestsTable tbody tr, #archivedTable tbody tr");
      rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
      });
    });

    function moveToArchived(button, newStatus, newClass) {
      let row = button.closest("tr");
      let statusCell = row.querySelector(".status");
      statusCell.textContent = newStatus;
      statusCell.className = "status " + newClass;
      row.querySelector("td:last-child").remove();
      document.querySelector("#archivedTable tbody").appendChild(row);
      updateNotifCount();
    }

    function editStatus(button) {
      let row = button.closest("tr");
      let statusCell = row.querySelector(".status");
      let current = statusCell.textContent.toLowerCase();

      let dropdown = document.createElement("select");
      dropdown.className = "status-dropdown";
      dropdown.innerHTML = `
        <option value="pending" ${current==="pending"?"selected":""}>Pending</option>
        <option value="processing" ${current==="processing"?"selected":""}>Processing</option>
      `;

      statusCell.innerHTML = "";
      statusCell.appendChild(dropdown);
      dropdown.focus();

      dropdown.addEventListener("change", function () {
        let newValue = dropdown.value;
        if (confirm("Change status to " + newValue + "?")) {
          if (newValue === "pending") {
            statusCell.textContent = "Pending";
            statusCell.className = "status pending";
          } else if (newValue === "processing") {
            statusCell.textContent = "Processing";
            statusCell.className = "status processing";
          }
          updateNotifCount();
        } else {
          statusCell.textContent = current.charAt(0).toUpperCase() + current.slice(1);
          statusCell.className = "status " + current;
        }
      });

      dropdown.addEventListener("blur", function () {
        if (statusCell.contains(dropdown)) {
          statusCell.textContent = current.charAt(0).toUpperCase() + current.slice(1);
          statusCell.className = "status " + current;
        }
      });
    }

    document.querySelectorAll(".approve").forEach(btn=>{
      btn.addEventListener("click",()=>moveToArchived(btn,"Approved","approved"));
    });
    document.querySelectorAll(".decline").forEach(btn=>{
      btn.addEventListener("click",()=>moveToArchived(btn,"Declined","declined"));
    });
    document.querySelectorAll(".edit").forEach(btn=>{
      btn.addEventListener("click",()=>editStatus(btn));
    });

    if (performance.navigation.type === 2) {  
    // Back/forward navigation
    window.location.href = "../login.php"; // ← go up one folder
}

  </script>

</body>
</html>
