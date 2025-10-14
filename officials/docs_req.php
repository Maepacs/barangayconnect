<?php
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Protect officials dashboard
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Official") {
    header("Location: ../login.php");
    exit;
}

// Include database connection
include '../cons/config.php'; // make sure this file sets $conn (mysqli)

//
// Fetch Active Requests (Pending / Processing)
//
$activeQuery = "
    SELECT 
        dr.request_id,
        dr.user_id,
        u.fullname,
        dr.document_type,
        dr.purpose,
        dr.supporting_file,
        dr.tracking_number,
        dr.date_requested,
        dr.status
    FROM document_request dr
    LEFT JOIN users u ON dr.user_id = u.user_id
    WHERE dr.status IN ('Pending', 'Processing')
    ORDER BY dr.date_requested DESC
";
$activeResult = $conn->query($activeQuery);
if ($activeResult === false) {
    // For debugging (remove or log in production)
    die("DB error (active): " . $conn->error);
}

//
// Fetch Archived Requests (Approved / Declined)
//
$archivedQuery = "
    SELECT 
        dr.request_id,
        dr.user_id,
        u.fullname,
        dr.document_type,
        dr.purpose,
        dr.supporting_file,
        dr.tracking_number,
        dr.date_requested,
        dr.status
    FROM document_request dr
    LEFT JOIN users u ON dr.user_id = u.user_id
    WHERE dr.status IN ('Approved', 'Declined')
    ORDER BY dr.date_requested DESC
";
$archivedResult = $conn->query($archivedQuery);
if ($archivedResult === false) {
    die("DB error (archived): " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Officials Dashboard</title>
  <link rel="icon" href="../assets/images/bg_logo.png">
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

/* -------------------
   File Preview Modal
-------------------- */
.modal {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  background-color: rgba(0, 0, 0, 0.7);
}

.modal-content {
  position: relative;
  margin: 50px auto;
  background-color: #fff;
  border-radius: 10px;
  padding: 10px;
  width: 80%;
  max-width: 900px;
  height: 80%;
  display: flex;
  flex-direction: column;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
}

#fileContainer {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}

#fileContainer iframe {
  width: 100%;
  height: 100%;
  border: none;
  border-radius: 8px;
}

#fileContainer img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  border-radius: 8px;
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 20px;
  font-size: 28px;
  color: #333;
  cursor: pointer;
  z-index: 10000;
}

.close-btn:hover {
  color: red;
}

</style>
</head>
<body>
   <!-- Sidebar -->
   <div class="sidebar">
    <h2>Barangay Connect</h2><br>
    <img src="../assets/images/bg_logo.png" alt="Barangay Logo">
    <ul>
      <li><a href="officials_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
      <li><a href="docs_req.php" class="active"> <i class="fa-solid fa-file-lines"></i> Document Requests</a></li>
      <li><a href="complaints.php"><i class="fa-solid fa-comments"></i> Complaints</a></li>
      <li><a href="residents.php"><i class="fa-solid fa-users"></i> Residents</a></li>
      <li><a href="sms_history.php"><i class="fa-solid fa-message"></i> SMS History</a></li>
      <li><a href="activity_logs.php"><i class="fa-solid fa-list-check"></i> Activity Logs</a></li>
      <li><a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
      <li><a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
  </div>

    <!-- Main Content -->
    <div class="main-content">
    <!-- Header -->
    <div class="header">
      <h1>Officials Dashboard</h1>
      <div class="right-section">
        <div class="notification">
          <i class="fa-solid fa-bell"></i>
          <span class="badge" id="notifCount">0</span>
        </div>
        <div class="user">
          <i class="fa-solid fa-user-circle"></i>
          <span><?php echo htmlspecialchars($_SESSION["fullname"]) . " / " . htmlspecialchars($_SESSION["role"]); ?></span>
        </div>
      </div>
    </div>

    <!-- Search -->
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search document requests...">
      <button><i class="fa fa-search"></i></button>
    </div>

    <!-- ACTIVE REQUESTS TABLE -->
    <h2 class="section-title">Active Document Requests</h2>
    <div class="table-container">
      <table id="requestsTable">
        <thead>
          <tr>
            <th>Request ID</th>
            <th>Resident Name</th>
            <th>Document Type</th>
            <th>Purpose</th>
            <th>Supporting File</th>
            <th>Tracking #</th>
            <th>Date Requested</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($activeResult && $activeResult->num_rows > 0): ?>
            <?php while($row = $activeResult->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['request_id']); ?></td>
                <td><?php echo htmlspecialchars($row['fullname'] ?? 'Unknown'); ?></td>
                <td><?php echo htmlspecialchars($row['document_type']); ?></td>
                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                <td>
  <?php if (!empty($row['supporting_file'])): ?>
    <button class="action-btn view" onclick="openFileModal('<?php echo '../uploads/documents/' . htmlspecialchars($row['supporting_file']); ?>')">
      View File
    </button>
  <?php else: ?>
    None
  <?php endif; ?>
</td>

                <td><?php echo htmlspecialchars($row['tracking_number']); ?></td>
                <td><?php echo htmlspecialchars($row['date_requested']); ?></td>
                <td>
                  <span class="status <?php echo strtolower($row['status']); ?>">
                    <?php echo ucfirst($row['status']); ?>
                  </span>
                </td>
                <td>
                  <button class="action-btn approve" onclick="updateStatus(<?php echo $row['request_id']; ?>, 'Approved')">Approve</button>
                  <button class="action-btn decline" onclick="updateStatus(<?php echo $row['request_id']; ?>, 'Declined')">Decline</button>
                  <button class="action-btn archive" onclick="updateStatus(<?php echo $row['request_id']; ?>, 'Archived')">Archive</button>
                  <button class="action-btn view" onclick="viewRequest(<?php echo $row['request_id']; ?>)">View</button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="9" style="text-align:center;">No active document requests found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- ARCHIVED REQUESTS TABLE -->
    <h2 class="section-title">Archived Document Requests</h2>
    <div class="table-container">
      <table id="archivedTable">
        <thead>
          <tr>
            <th>Request ID</th>
            <th>Resident Name</th>
            <th>Document Type</th>
            <th>Purpose</th>
            <th>Supporting File</th>
            <th>Tracking #</th>
            <th>Date Requested</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($archivedResult && $archivedResult->num_rows > 0): ?>
            <?php while($row = $archivedResult->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['request_id']); ?></td>
                <td><?php echo htmlspecialchars($row['fullname'] ?? 'Unknown'); ?></td>
                <td><?php echo htmlspecialchars($row['document_type']); ?></td>
                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                <td>
                  <?php if (!empty($row['supporting_file'])): ?>
                    <a href="../uploads/supporting_files/<?php echo htmlspecialchars($row['supporting_file']); ?>" target="_blank">View File</a>
                  <?php else: ?>
                    None
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['tracking_number']); ?></td>
                <td><?php echo htmlspecialchars($row['date_requested']); ?></td>
                <td><span class="status archived"><?php echo htmlspecialchars($row['status']); ?></span></td>
                <td>
                  <button class="action-btn restore" onclick="updateStatus(<?php echo $row['request_id']; ?>, 'Pending')">Restore</button>
                  <button class="action-btn view" onclick="viewRequest(<?php echo $row['request_id']; ?>)">View</button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="9" style="text-align:center;">No archived requests found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <!-- File Preview Modal -->
<div id="fileModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeFileModal()">&times;</span>
    <div id="fileContainer"></div>
  </div>
</div>



  <script>
    // Search filter
    document.getElementById("searchInput").addEventListener("keyup", function() {
      let filter = this.value.toLowerCase();
      let rows = document.querySelectorAll("#requestsTable tbody tr, #archivedTable tbody tr");
      rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
      });
    });

    // Notification badge count
    function updateNotifCount() {
      const badge = document.getElementById("notifCount");
      const active = document.querySelectorAll(".status.pending, .status.processing");
      badge.textContent = active.length;
    }
    updateNotifCount();

    // AJAX: update status
    function updateStatus(request_id, newStatus) {
      if (!confirm("Are you sure you want to mark this request as " + newStatus + "?")) return;

      const xhr = new XMLHttpRequest();
      xhr.open("POST", "update_status.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onload = function() {
        if (xhr.status === 200) {
          alert(xhr.responseText);
          location.reload();
        } else {
          alert("Error updating status.");
        }
      };
      xhr.send("request_id=" + request_id + "&status=" + encodeURIComponent(newStatus));
    }

    function viewRequest(id) {
      // you asked before for a modal view — if you want modal, I can add it next
      window.location.href = "view_request.php?request_id=" + id;
    }

    function openFileModal(filePath) {
  const modal = document.getElementById("fileModal");
  const container = document.getElementById("fileContainer");
  container.innerHTML = ""; // clear old content

  // Detect file type
  const ext = filePath.split('.').pop().toLowerCase();
  let element;

  if (["jpg", "jpeg", "png", "gif", "webp"].includes(ext)) {
    // Show image
    element = document.createElement("img");
    element.src = filePath;
    element.alt = "Supporting File";
  } else if (ext === "pdf") {
    // Show PDF
    element = document.createElement("iframe");
    element.src = filePath;
  } else {
    // Unsupported file type (e.g. docx)
    element = document.createElement("p");
    element.textContent = "Preview not available. Click below to download:";
    const downloadLink = document.createElement("a");
    downloadLink.href = filePath;
    downloadLink.textContent = "Download File";
    downloadLink.target = "_blank";
    downloadLink.style.color = "#007bff";
    downloadLink.style.display = "block";
    downloadLink.style.marginTop = "10px";
    container.appendChild(element);
    container.appendChild(downloadLink);
    modal.style.display = "block";
    return;
  }

  container.appendChild(element);
  modal.style.display = "block";
}

function closeFileModal() {
  const modal = document.getElementById("fileModal");
  const container = document.getElementById("fileContainer");
  container.innerHTML = "";
  modal.style.display = "none";
}

// Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById("fileModal");
  if (event.target === modal) closeFileModal();
};

// ✅ Close modal when pressing ESC key
document.addEventListener("keydown", function(event) {
  const modal = document.getElementById("fileModal");
  if (event.key === "Escape" && modal.style.display === "block") {
    closeFileModal();
  }
});



  </script>
</body>
</html>
