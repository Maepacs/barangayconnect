<?php
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Protect dashboard
if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php"); // ← go up one folder
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Officials</title>
  <link rel="icon" href="../assets/images/ghost.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
/* --------------------
   RESET & BASE STYLES
-------------------- */
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

/* --------------------
   SIDEBAR
-------------------- */
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


/* --------------------
   MAIN CONTENT
-------------------- */
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

/* --------------------
   TOP BAR (Add + Search)
-------------------- */
.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 20px 0 10px 0;
  flex-wrap: wrap;
  gap: 10px;
}

.top-bar .btn-add {
  flex-shrink: 0;
}

.search-bar {
  display: flex;
  gap: 0;
  flex-shrink: 0;
}

.search-bar input {
  padding: 8px 12px;
  border-radius: 6px 0 0 6px;
  border: 1px solid #ccc;
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

.search-bar button i {
  font-size: 14px;
}

/* --------------------
   BUTTONS
-------------------- */
.btn {
  padding: 8px 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
}

.btn-add { background: #2ECC71; color: white; font-weight: bold; }
.btn-edit { background: #F39C12; color: white; }
.btn-delete { background: #E74C3C; color: white; }
.btn-close { background: #E74C3C; color: #fff; float: right; }
.btn-submit { background: #2ECC71; color: #fff; }

/* --------------------
   TABLE
-------------------- */
.table-container {
  background: rgba(255, 255, 255, 0.08);
  padding: 20px;
  border-radius: 10px;
  overflow-x: auto;
  margin-top: 10px;
}

table {
  width: 100%;
  border-collapse: collapse;
  color: #fff;
}

th, td {
  padding: 12px 15px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  text-align: left;
  font-size: 14px;
}

th {
  background: rgba(74, 144, 226, 0.6);
}

/* --------------------
   MODAL
-------------------- */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: #fff;
  color: #333;
  padding: 20px;
  border-radius: 10px;
  width: 400px;
  max-width: 90%;
  position: relative;
}

.modal-content h2 {
  margin-bottom: 15px;
}

.modal-content label {
  display: block;
  margin: 10px 0 5px;
  font-size: 14px;
}

.modal-content input,
.modal-content select {
  width: 100%;
  padding: 8px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

/* --------------------
   UTILITIES
-------------------- */
.hidden { display: none !important; }

  </style>
</head>
<body>

  <!-- SIDEBAR -->
  <div class="sidebar">
    <h2>Barangay Connect</h2><br>
    <img src="../assets/images/bg_logo.png">
    <ul>
      <li><a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
      <li><a href="document_request.php"><i class="fa-solid fa-file-lines"></i> Document Requests</a></li>
      <li><a href="complaints.php"><i class="fa-solid fa-comments"></i> Complaints</a></li>
      <li><a href="residents.php"><i class="fa-solid fa-users"></i> Residents</a></li>
      <li><a href="officials.php" class="active"><i class="fa-solid fa-user-shield"></i> Officials</a></li>
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

    <!-- Top Bar: Add + Search -->
    <div class="top-bar">
      <button class="btn btn-add" onclick="openModal()">+ Add Official</button>
      <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search officials...">
        <button class="btn"><i class="fa fa-search"></i></button>
      </div>
    </div>

    <!-- Officials Table -->
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Position</th>
            <th>Term Start</th>
            <th>Term End</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="officialsTable">
          <tr>
            <td>O1</td>
            <td>Juan Dela Cruz</td>
            <td>Barangay Captain</td>
            <td>2022-07-01</td>
            <td>2025-07-01</td>
            <td>
              <button class="btn btn-edit">Edit</button>
              <button class="btn btn-delete">Delete</button>
            </td>
          </tr>
          <tr>
            <td>O2</td>
            <td>Maria Santos</td>
            <td>Kagawad</td>
            <td>2022-07-01</td>
            <td>2025-07-01</td>
            <td>
              <button class="btn btn-edit">Edit</button>
              <button class="btn btn-delete">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- MODAL: Add Official -->
  <div class="modal" id="officialModal">
    <div class="modal-content">
      <button class="btn btn-close" onclick="closeModal()">X</button>
      <h2>Add Official</h2>
      <form id="officialForm">
        <label for="name">Full Name</label>
        <input type="text" id="name" required>

        <label for="position">Position</label>
        <select id="position" required>
          <option value="">Select position</option>
          <option>Barangay Captain</option>
          <option>Kagawad</option>
          <option>Secretary</option>
          <option>Treasurer</option>
          <option>SK Chairman</option>
        </select>

        <label for="termStart">Term Start</label>
        <input type="date" id="termStart" required>

        <label for="termEnd">Term End</label>
        <input type="date" id="termEnd" required>

        <button type="submit" class="btn btn-submit">Save</button>
      </form>
    </div>
  </div>

  <script>
    const modal = document.getElementById("officialModal");
    const form = document.getElementById("officialForm");
    const table = document.getElementById("officialsTable");
    const searchInput = document.getElementById("searchInput");

    function openModal() { modal.style.display = "flex"; }
    function closeModal() { modal.style.display = "none"; }

    // Add Official
    form.addEventListener("submit", function(e){
      e.preventDefault();
      const name = document.getElementById("name").value;
      const position = document.getElementById("position").value;
      const start = document.getElementById("termStart").value;
      const end = document.getElementById("termEnd").value;

      // Generate ID with 'O' prefix
      let nextIdNumber = table.rows.length + 1;
      let officialId = "O" + nextIdNumber;

      const row = table.insertRow();
      row.innerHTML = `
        <td>${officialId}</td>
        <td>${name}</td>
        <td>${position}</td>
        <td>${start}</td>
        <td>${end}</td>
        <td>
          <button class="btn btn-edit">Edit</button>
          <button class="btn btn-delete">Delete</button>
        </td>
      `;
      form.reset();
      closeModal();
    });

    // Close modal if clicked outside
    window.onclick = function(e){
      if(e.target==modal) closeModal();
    }

    // Search functionality
    searchInput.addEventListener("keyup", function(){
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll("#officialsTable tr");
      rows.forEach(row=>{
        row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
      });
    });

    if (performance.navigation.type === 2) {  
    // Back/forward navigation
    window.location.href = "../login.php"; // ← go up one folder
}

  </script>
</body>
</html>
