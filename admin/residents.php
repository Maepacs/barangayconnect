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
  <title>Barangay Connect | Residents</title>
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

/* ==============================
   Sidebar
============================== */
/* Sidebar */
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

/* ==============================
   Main Content
============================== */
.main-content {
  margin-left: 250px;
  flex: 1;
  display: flex;
  flex-direction: column;
  background: rgba(0, 0, 0, 0.55);
  padding: 20px;
}

/* ==============================
   Header
============================== */
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
  color: white;
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
}

/* ==============================
   Search Bar
============================== */
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

/* ==============================
   Table
============================== */
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

/* ==============================
   Buttons
============================== */
.action-btn {
  padding: 6px 10px;
  margin: 2px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  color: white;
}

.edit   { background: #F39C12; }
.view   { background: #3498DB; }
.delete { background: #E74C3C; }

/* ==============================
   Section Titles
============================== */
h2.section-title {
  margin: 15px 0;
  font-size: 18px;
  color: #FFD54F;
}

/* ==============================
   Modal
============================== */
.modal {
  display: none;
  position: fixed;
  z-index: 999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.7);
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: #fff;
  color: #000;
  padding: 20px;
  border-radius: 10px;
  width: 400px;
  max-width: 90%;
}

.modal-content h3 {
  margin-bottom: 15px;
}

.modal-close {
  float: right;
  cursor: pointer;
  font-weight: bold;
}

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
      <li><a href="complaints.php"><i class="fa-solid fa-comments"></i> Complaints</a></li>
      <li><a href="residents.php" class="active"><i class="fa-solid fa-users"></i> Residents</a></li>
      <li><a href="officials.php"><i class="fa-solid fa-user-shield"></i> Officials</a></li>
      <li><a href="create_officials.php"><i class="fas fa-user-plus"></i> Create Official Account</a></li>
      <li><a href="sms_history.php"><i class="fa-solid fa-message"></i> SMS History</a></li>
      <li><a href="activity_logs.php"> <i class="fa-solid fa-list-check"></i> Activity Logs</a></li>
      <li><a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
      <li><a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
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
          <span>
            <?php 
              echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : "Guest"; 
            ?>
          </span>
        </div>
      </div>
    </div> <!-- ✅ Closed header properly -->

    <!-- Search Bar -->
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search residents...">
      <button><i class="fa fa-search"></i></button>
    </div>

    <!-- Residents Table -->
    <h2 class="section-title">Resident List</h2>
    <div class="table-container">
      <table id="residentsTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Resident Name</th>
            <th>Address</th>
            <th>Contact</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>R1</td>
            <td>Pedro Reyes</td>
            <td>Purok 1</td>
            <td>09123456789</td>
            <td>
              <button class="action-btn edit">Edit</button>
              <button class="action-btn view">View</button>
              <button class="action-btn delete">Delete</button>
            </td>
          </tr>
          <tr>
            <td>R2</td>
            <td>Ana Lopez</td>
            <td>Purok 2</td>
            <td>09987654321</td>
            <td>
              <button class="action-btn edit">Edit</button>
              <button class="action-btn view">View</button>
              <button class="action-btn delete">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal" id="viewModal">
    <div class="modal-content">
      <span class="modal-close" onclick="closeModal()">&times;</span>
      <h3>Resident Details</h3>
      <p id="modalBody">Loading...</p>
    </div>
  </div>

<script>
  // Search filter
  document.getElementById("searchInput").addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#residentsTable tbody tr");
    rows.forEach(row => {
      let text = row.innerText.toLowerCase();
      row.style.display = text.includes(filter) ? "" : "none";
    });
  });

  // View button (modal)
  document.querySelectorAll(".view").forEach(btn=>{
    btn.addEventListener("click", function(){
      let row = this.closest("tr").children;
      let details = `
        <strong>ID:</strong> ${row[0].innerText}<br>
        <strong>Name:</strong> ${row[1].innerText}<br>
        <strong>Address:</strong> ${row[2].innerText}<br>
        <strong>Contact:</strong> ${row[3].innerText}
      `;
      document.getElementById("modalBody").innerHTML = details;
      document.getElementById("viewModal").style.display = "flex";
    });
  });

  function closeModal(){document.getElementById("viewModal").style.display="none";}

  // Delete button (confirmation)
  document.querySelectorAll(".delete").forEach(btn=>{
    btn.addEventListener("click", function(){
      if(confirm("Are you sure you want to delete this resident?")){
        this.closest("tr").remove();
      }
    });
  });

  // Edit button (placeholder logic, you can link to edit form)
  document.querySelectorAll(".edit").forEach(btn=>{
    btn.addEventListener("click", function(){
      let name = this.closest("tr").children[1].innerText;
      alert("Edit resident: " + name);
    });
  });

  if (performance.navigation.type === 2) {  
    // Back/forward navigation
    window.location.href = "../login.php"; // ← go up one folder
}

</script>
</body>
</html>
