<?php
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Protect admin dashboard
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../login.php");
    exit;
}

require_once("../cons/config.php");

// Fetch all users with role 'Official'
$officialUsers = [];
$sql = "SELECT user_id, fullname FROM users WHERE role = 'Official' ORDER BY fullname ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $officialUsers[] = $row;
    }
}

// ✅ Handle form submission (no AJAX)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'] ?? '';
    $position = $_POST['position'] ?? '';
    $term_start = $_POST['termStart'] ?? '';
    $term_end = $_POST['termEnd'] ?? '';
    $added_by = $_SESSION['user_id'];

    if (!empty($user_id) && !empty($position) && !empty($term_start) && !empty($term_end)) {
        // Check if already official
        $check = $conn->prepare("SELECT 1 FROM barangay_officials WHERE user_id = ?");
        $check->bind_param("s", $user_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 0) {
            // Generate new Official ID
            $query = $conn->query("SELECT official_id FROM barangay_officials ORDER BY CAST(SUBSTRING(official_id, 3) AS UNSIGNED) DESC LIMIT 1");
            if ($query && $query->num_rows > 0) {
                $row = $query->fetch_assoc();
                $lastNum = (int)substr($row['official_id'], 2);
                $newOfficialId = 'BO' . str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);
            } else {
                $newOfficialId = 'BO000001';
            }

            // Insert new official
            $stmt = $conn->prepare("INSERT INTO barangay_officials (official_id, user_id, position, term_start, term_end) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $newOfficialId, $user_id, $position, $term_start, $term_end);

            if ($stmt->execute()) {
/** Generate log_id **/
$logRes = $conn->query("SELECT log_id FROM activity_logs ORDER BY CAST(SUBSTRING(log_id, 4) AS UNSIGNED) DESC LIMIT 1");
if ($logRes && $logRow = $logRes->fetch_assoc()) {
    $lastLogNum = (int)substr($logRow["log_id"], 3);
    $newLogNum  = $lastLogNum + 1;
} else {
    $newLogNum = 1;
}

$log_id = "LOG" . str_pad($newLogNum, 6, "0", STR_PAD_LEFT);

/** Insert into activity_logs **/
$admin_name = $_SESSION['fullname'] ?? 'Unknown Admin';
$added_by   = $_SESSION['user_id'] ?? 'Unknown';
$action     = "Admin $admin_name added a new Barangay Official ($newOfficialId)";
$created_at = date("Y-m-d H:i:s");

$logStmt = $conn->prepare("INSERT INTO activity_logs (log_id, user_id, action, created_at) VALUES (?, ?, ?, ?)");
$logStmt->bind_param("ssss", $log_id, $added_by, $action, $created_at);

if (!$logStmt->execute()) {
    echo "<script>alert('Activity log insert failed: " . addslashes($logStmt->error) . "');</script>";
}
$logStmt->close();


                echo "<script>alert('Official added successfully.'); window.location.href='officials.php';</script>";
                exit;
            } else {
                echo "<script>alert('Error inserting official: " . addslashes($stmt->error) . "');</script>";
            }

            $stmt->close();
        } else {
            echo "<script>alert('This user is already an official.'); window.location.href='officials.php';</script>";
        }

        $check->close();
    } else {
        echo "<script>alert('Please fill in all fields.');</script>";
    }
}

// Fetch existing officials for table
$officialsData = [];
$sql = "SELECT bo.official_id, u.fullname, bo.position, bo.term_start, bo.term_end
        FROM barangay_officials bo
        JOIN users u ON bo.user_id = u.user_id
        ORDER BY bo.official_id ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $officialsData[] = $row;
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Officials</title>
  <link rel="icon" href="../assets/images/BG_logo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
/* RESET & BASE STYLES */
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
.sidebar ul li a:hover,
.sidebar ul li a.active {
  background: #4a90e2;
  color: #fff;
}

/* MAIN CONTENT */
.main-content {
  margin-left: 250px;
  flex: 1;
  display: flex;
  flex-direction: column;
  background: rgba(0, 0, 0, 0.55);
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
.header .right-section { display: flex; align-items: center; gap: 20px; }
.notification { position: relative; cursor: pointer; }
.notification i { font-size: 20px; color: #f2f5f8; }
.notification .badge {
  position: absolute;
  top: -5px;
  right: -8px;
  background: #d55d2e;
  color: white;
  font-size: 12px;
  font-weight: bold;
  padding: 2px 6px;
  border-radius: 50%;
}
.header .user { display: flex; align-items: center; gap: 10px; }
.header .user i { font-size: 20px; color: #e9edf1; }

/* TOP BAR */
/* .top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 20px 0 10px 0;
  flex-wrap: wrap;
  gap: 10px;
} */

.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 20px 0 10px 0;
  flex-wrap: wrap;
  gap: 10px;
}

/* Left buttons area */
.left-buttons {
  display: flex;
  gap: 10px;
  align-items: center;
}

/* Base button style */
.btn {
  padding: 8px 14px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  text-decoration: none;
  color: white;
  font-weight: bold;
  transition: 0.3s;
}

/* Add Official button */
.btn-add {
  background: #2ECC71;
}

/* Create Account button (blue variant) */
.btn-account {
  background: #3498DB;
}

/* Hover effects */
.btn:hover {
  opacity: 0.85;
}

/* Search bar */
.search-bar {
  display: flex;
  align-items: center;
  gap: 5px;
}

.search-bar input {
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 14px;
}

.search-bar .btn {
  background: #4a90e2;
  color: white;
}
.btn { padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
.btn-add { background: #2ECC71; color: white; font-weight: bold; }
.btn-edit { background: #F39C12; color: white; }
.btn-delete { background: #E74C3C; color: white; }
.btn-close { background: #E74C3C; color: #fff; float: right; }
.btn-submit { background: #2ECC71; color: #fff; }

/* TABLE */
.table-container {
  background: rgba(255, 255, 255, 0.08);
  padding: 20px;
  border-radius: 10px;
  overflow-x: auto;
  margin-top: 10px;
}
table { width: 100%; border-collapse: collapse; color: #fff; }
th, td {
  padding: 12px 15px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  text-align: left;
  font-size: 14px;
}
th { background: rgba(74, 144, 226, 0.6); }

/* MODAL */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0; top: 0;
  width: 100%; height: 100%;
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
.modal-content h2 { margin-bottom: 15px; }
.modal-content label { display: block; margin: 10px 0 5px; font-size: 14px; }
.modal-content input,
.modal-content select {
  width: 100%;
  padding: 8px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 6px;
}
  </style>
</head>
<body>

  <!-- SIDEBAR -->
  <div class="sidebar">
    <h2>Barangay Connect</h2><br>
    <img src="../assets/images/bg_logo.png" alt="Barangay Logo">
    <ul>
      <li><a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
      <li><a href="document_request.php"><i class="fa-solid fa-file-lines"></i> Document Requests</a></li>
      <li><a href="complaints.php"><i class="fa-solid fa-comments"></i> Complaints</a></li>
      <li><a href="residents.php"><i class="fa-solid fa-users"></i> Residents</a></li>
      <li><a href="household.php"><i class="fa-solid fa-people-roof"></i> Household Records</a></li>
      <li><a href="officials.php" class="active"><i class="fa-solid fa-user-shield"></i> Officials</a></li>
      <li><a href="compose_message.php"><i class="fa-solid fa-pen-to-square"></i> Compose Message</a></li>
      <li><a href="sms_history.php"><i class="fa-solid fa-message"></i> SMS History</a></li>
      <li><a href="activity_logs.php"><i class="fa-solid fa-list-check"></i> Activity Logs</a></li>
      <li><a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
      <li><a href="landing_page.php"><i class="fa-solid fa-house"></i> Landing Page View</a></li>
      <li><a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');">
        <i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
  </div>

  <!-- MAIN CONTENT -->
  <div class="main-content">
    <div class="header">
      <h1>Admin Dashboard</h1>
      <div class="right-section">
        <div class="notification">
          <i class="fa-solid fa-bell"></i>
          <span class="badge">#</span>
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
    <div class="top-bar">
  <button class="btn btn-add" onclick="openModal()">+ Add Official</button>

  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search officials...">
    <button class="btn"><i class="fa fa-search"></i></button>
  </div>
</div>



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
  <?php if (!empty($officialsData)): ?>
    <?php foreach ($officialsData as $index => $official): ?>
      <tr>
        <td><?= htmlspecialchars($official['official_id']) ?></td>
        <td><?= htmlspecialchars($official['fullname']) ?></td>
        <td><?= htmlspecialchars($official['position']) ?></td>
        <td><?= htmlspecialchars($official['term_start']) ?></td>
        <td><?= htmlspecialchars($official['term_end']) ?></td>
        <td>
          <button class="btn btn-edit">Edit</button>
          <button class="btn btn-delete">Delete</button>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr><td colspan="6" style="text-align:center;">No officials found</td></tr>
  <?php endif; ?>
</tbody>

      </table>
    </div>

  <div class="top-bar">
  <a href="create_officials.php" class="btn btn-add">Create Official Account</a>
  </div>

</div>
  <!-- MODAL -->
  <div class="modal" id="officialModal">
    <div class="modal-content">
      <button class="btn btn-close" onclick="closeModal()">X</button>
      <h2>Add Official</h2>
      <form method="POST" action="">
        <label for="name">Full Name</label>
        <select id="name" name="user_id" required>
          <option value="">Select Official</option>
          <?php foreach ($officialUsers as $user): ?>
            <option value="<?= htmlspecialchars($user['user_id']) ?>">
              <?= htmlspecialchars($user['fullname']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label for="position">Position</label>
        <select id="position" name="position" required>
          <option value="">Select position</option>
          <option>Barangay Captain</option>
          <option>Kagawad</option>
          <option>Secretary</option>
          <option>Treasurer</option>
          <option>SK Chairman</option>
        </select>

        <label for="termStart">Term Start</label>
        <input type="date" id="termStart" name="termStart" required>

        <label for="termEnd">Term End</label>
        <input type="date" id="termEnd" name="termEnd" required>

        <button type="submit" class="btn btn-submit">Save</button>
      </form>
    </div>
  </div>

  <script>
  const modal = document.getElementById("officialModal");

  function openModal() {
    modal.style.display = "flex";
  }

  function closeModal() {
    modal.style.display = "none";
  }

  // Optional: Close modal when clicking outside of modal content
  window.onclick = function(event) {
    if (event.target === modal) {
      closeModal();
    }
  }

  // Search function (your existing one)
  const searchInput = document.getElementById("searchInput");
  searchInput.addEventListener("keyup", function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll("#officialsTable tr").forEach(row => {
      row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
    });
  });
</script>

</body>
</html>
