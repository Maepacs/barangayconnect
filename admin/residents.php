<?php
require_once "../cons/config.php";
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Protect admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
  header("Location: ../login.php");
  exit;
}

$query = "SELECT user_id, fullname, username, role, status FROM users";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | User Accounts</title>
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

    /* ================= Sidebar ================= */
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

    .sidebar ul li a:hover,
    .sidebar ul li a.active {
      background: #4a90e2;
      color: #fff;
    }

    /* ================= Main Content ================= */
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

    /* ================= Search Bar ================= */
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

    /* ================= Table ================= */
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

    /* ================= Buttons ================= */
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

    /* ============ Modal Styling ============ */
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
    padding: 25px;
    border-radius: 15px;
    width: 500px;
    max-width: 90%;
    position: relative;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    animation: fadeIn 0.3s ease;
  }

  .modal-close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 22px;
    font-weight: bold;
    color: #555;
    cursor: pointer;
  }

  .modal-close:hover {
    color: #e74c3c;
  }

  .modal-content h3 {
    margin-bottom: 15px;
    text-align: center;
    color: #2c3e50;
  }

  /* ============ Card Layout ============ */
  .resident-card {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px 20px;
    background: #f9f9f9;
    padding: 15px 20px;
    border-radius: 10px;
    font-size: 14px;
  }

  .resident-card div {
    background: #ffffff;
    padding: 8px 10px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
  }

  .resident-card strong {
    display: block;
    color: #34495e;
    margin-bottom: 3px;
    font-weight: 600;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Barangay Connect</h2><br>
    <img src="../assets/images/bg_logo.png" alt="Logo">
    <ul>
      <li><a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
      <li><a href="document_request.php"><i class="fa-solid fa-file-lines"></i> Document Requests</a></li>
      <li><a href="complaints.php"><i class="fa-solid fa-comments"></i> Complaints</a></li>
      <li><a href="residents.php" class="active"> <i class="fa-solid fa-users"></i> Residents</a></li>
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
          <span class="badge">#</span>
        </div>
        <div class="user">
          <i class="fa-solid fa-user-circle"></i>
          <span>
            <?php 
              echo htmlspecialchars($_SESSION["fullname"]) . " / " . htmlspecialchars($_SESSION["role"]);
            ?>
          </span>
        </div>
      </div>
    </div>

    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search users...">
      <button><i class="fa fa-search"></i></button>
    </div>

    <h2 class="section-title">User Accounts</h2>
    <div class="table-container">
      <table id="usersTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Full Name</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['user_id']) ?></td>
                <td><?= htmlspecialchars($row['fullname']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                <td><?= ucfirst(htmlspecialchars($row['status'])) ?></td>
                <td>
                  <button class="action-btn edit">Edit</button>
                  <button class="action-btn view" data-id="<?= $row['user_id']; ?>">View</button>
                  <button class="action-btn delete">Delete</button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No users found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div class="modal" id="viewModal">
      <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h3>Resident Details</h3>
        <div id="modalBody" class="resident-card">Loading...</div>
      </div>
    </div>
  </div>

  <script>
  // Search
  document.getElementById("searchInput").addEventListener("keyup", function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll("#usersTable tbody tr").forEach(row => {
      row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
    });
  });

  // View Resident Profile
  document.querySelectorAll(".view").forEach(btn => {
    btn.addEventListener("click", function() {
      let userId = this.dataset.id;
      const modalBody = document.getElementById("modalBody");
      const modal = document.getElementById("viewModal");

      modalBody.innerHTML = "Loading...";
      modal.style.display = "flex";

      fetch("get_resident_profile.php?id=" + encodeURIComponent(userId))
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const r = data.resident;
            modalBody.innerHTML = `
              <strong>Resident ID:</strong> ${r.resident_id}<br>
              <strong>Full Name:</strong> ${r.fullname}<br>
              <strong>Address:</strong> ${r.address}<br>
              <strong>Birthdate:</strong> ${r.birthdate}<br>
              <strong>Contact:</strong> ${r.contact}<br>
              <strong>Occupation:</strong> ${r.occupation || 'N/A'}<br>
              <strong>Gender:</strong> ${r.gender || 'N/A'}
            `;
          } else {
            modalBody.innerHTML = `<span style="color:red;">No profile found for this user.</span>`;
          }
        })
        .catch(err => {
          console.error(err);
          modalBody.innerHTML = `<span style="color:red;">Error loading resident details.</span>`;
        });
    });
  });

  // Close Modal
  function closeModal() {
    document.getElementById("viewModal").style.display = "none";
  }

  window.addEventListener("click", (e) => {
    const modal = document.getElementById("viewModal");
    if (e.target === modal) closeModal();
  });

  // Delete
  document.querySelectorAll(".delete").forEach(btn => {
    btn.addEventListener("click", function() {
      const user_id = this.closest("tr").children[0].innerText;
      if (confirm("Delete this user?")) {
        fetch("delete_user.php", {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: "id=" + encodeURIComponent(user_id)
        })
        .then(res => res.text())
        .then(alert)
        .then(() => location.reload());
      }
    });
  });

  // Edit
  document.querySelectorAll(".edit").forEach(btn => {
    btn.addEventListener("click", function() {
      const user_id = this.closest("tr").children[0].innerText;
      window.location.href = "edit_user.php?id=" + encodeURIComponent(user_id);
    });
  });
</script>
</body>
</html>
