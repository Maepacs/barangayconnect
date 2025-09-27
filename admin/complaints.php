<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Complaints</title>
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

    .sidebar h2 {
      text-align: center;
      font-size: 22px;
      color: #ffffff;
      margin-bottom: 15px;
    }

    .sidebar img {
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

    .sidebar ul li a:hover,
    .sidebar ul li a.active {
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
      color: #fff;
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
      color: #fff;
    }

    /* Search bar */
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

    /* Table */
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

    th {
      background: rgba(74,144,226,0.6);
      font-weight: bold;
    }

    tr:hover {
      background: rgba(255,255,255,0.1);
    }

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

    /* Status Labels */
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

    h2.section-title {
      margin: 15px 0;
      font-size: 18px;
      color: #FFD54F;
    }

      /* Modern styled dropdown */
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
      <li><a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
      <li><a href="document_request.php"><i class="fa-solid fa-file-lines"></i> Document Requests</a></li>
      <li><a href="complaints.php" class="active"><i class="fa-solid fa-comments"></i> Complaints</a></li>
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
      <h1>Complaints</h1>
      <div class="right-section">
        <div class="notification">
          <i class="fa-solid fa-bell"></i>
          <span class="badge" id="notifCount">0</span>
        </div>
        <div class="user">
          <i class="fa-solid fa-user-circle"></i>
          <span>Admin</span>
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
            <th>Date Filed</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>Pedro Reyes</td>
            <td>Loud noise during midnight</td>
            <td>2025-09-25</td>
            <td><span class="status pending">Pending</span></td>
            <td>
              <button class="action-btn resolve">Resolve</button>
              <button class="action-btn dismiss">Dismiss</button>
              <button class="action-btn edit">Edit</button>
              <button class="action-btn view">View</button>
            </td>
          </tr>
          <tr>
            <td>2</td>
            <td>Ana Lopez</td>
            <td>Improper garbage disposal</td>
            <td>2025-09-24</td>
            <td><span class="status ongoing">Ongoing</span></td>
            <td>
              <button class="action-btn resolve">Resolve</button>
              <button class="action-btn dismiss">Dismiss</button>
              <button class="action-btn edit">Edit</button>
              <button class="action-btn view">View</button>
            </td>
          </tr>
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
            <th>Date Filed</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>Juan Dela Cruz</td>
            <td>Physical altercation</td>
            <td>2025-09-20</td>
            <td><span class="status resolved">Resolved</span></td>
          </tr>
          <tr>
            <td>2</td>
            <td>Maria Santos</td>
            <td>Vandalism complaint</td>
            <td>2025-09-18</td>
            <td><span class="status dismissed">Dismissed</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Script -->
  <script>
  const notifBadge = document.getElementById("notifCount");

  function updateNotifCount() {
    let activeComplaints = document.querySelectorAll("#complaintsTable .status.pending, #complaintsTable .status.ongoing");
    notifBadge.textContent = activeComplaints.length;
  }

  // Assign IDs only once, not renumbering after move
  function assignComplaintIDs() {
    let allRows = document.querySelectorAll("#complaintsTable tbody tr, #archivedTable tbody tr");
    allRows.forEach((row, index) => {
      let idCell = row.querySelector("td:first-child");
      if (!idCell.dataset.fixed) {
        idCell.textContent = "C" + (index + 1);
        idCell.dataset.fixed = "true"; // lock the ID so it won’t change
      }
    });
  }

  updateNotifCount();
  assignComplaintIDs();

  // Search filter
  document.getElementById("searchInput").addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#complaintsTable tbody tr, #archivedTable tbody tr");
    rows.forEach(row => {
      let text = row.innerText.toLowerCase();
      row.style.display = text.includes(filter) ? "" : "none";
    });
  });

  // Move row to archived (keep ID the same)
  function moveToArchived(button, newStatus, newClass) {
    let row = button.closest("tr");
    let statusCell = row.querySelector(".status");
    statusCell.textContent = newStatus;
    statusCell.className = "status " + newClass;

    // remove action buttons
    row.querySelector("td:last-child").remove();

    // move row to archived
    document.querySelector("#archivedTable tbody").appendChild(row);

    updateNotifCount();
    // No renumbering — IDs stay fixed
  }

  // Edit status (modern dropdown + confirm)
  function editStatus(button) {
    let row = button.closest("tr");
    let statusCell = row.querySelector(".status");
    let current = statusCell.textContent.toLowerCase();

    // Create styled dropdown
    let dropdown = document.createElement("select");
    dropdown.className = "status-dropdown";
    dropdown.innerHTML = `
      <option value="pending" ${current === "pending" ? "selected" : ""}>Pending</option>
      <option value="ongoing" ${current === "ongoing" ? "selected" : ""}>Ongoing</option>
    `;

    statusCell.innerHTML = "";
    statusCell.appendChild(dropdown);
    dropdown.focus();

    dropdown.addEventListener("change", function () {
      let newValue = dropdown.value;
      if (confirm("Are you sure you want to change status to " + newValue.charAt(0).toUpperCase() + newValue.slice(1) + "?")) {
        if (newValue === "pending") {
          statusCell.textContent = "Pending";
          statusCell.className = "status pending";
        } else if (newValue === "ongoing") {
          statusCell.textContent = "Ongoing";
          statusCell.className = "status ongoing";
        }
        updateNotifCount();
      } else {
        statusCell.textContent = current.charAt(0).toUpperCase() + current.slice(1);
        statusCell.className = "status " + current;
      }
    });

    // revert if blurred without change
    dropdown.addEventListener("blur", function () {
      if (statusCell.contains(dropdown)) {
        statusCell.textContent = current.charAt(0).toUpperCase() + current.slice(1);
        statusCell.className = "status " + current;
      }
    });
  }

  // Button events
  document.querySelectorAll(".resolve").forEach(btn => {
    btn.addEventListener("click", function() {
      moveToArchived(this, "Resolved", "resolved");
    });
  });

  document.querySelectorAll(".dismiss").forEach(btn => {
    btn.addEventListener("click", function() {
      moveToArchived(this, "Dismissed", "dismissed");
    });
  });

  document.querySelectorAll(".edit").forEach(btn => {
    btn.addEventListener("click", function() {
      editStatus(this);
    });
  });
</script>

</body>
</html>
