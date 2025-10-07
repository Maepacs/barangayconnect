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

// ✅ Fetch fullname and role from users table
$stmt = $conn->prepare("SELECT fullname, role FROM users WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
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

// ✅ Fetch complaints for this user
$stmt = $conn->prepare("SELECT complaint_id, DATE(date_filed) AS filed_date, status 
                        FROM complaints 
                        WHERE user_id = ? 
                        ORDER BY date_filed DESC");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$complaintsResult = $stmt->get_result();
$stmt->close();

// ✅ Fetch document requests for this user
$stmt = $conn->prepare("SELECT request_id, document_type, status, date_requested
                        FROM document_request 
                        WHERE user_id = ? 
                        ORDER BY date_requested DESC");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$docRequestsResult = $stmt->get_result();
$stmt->close();

// ✅ Count records AFTER fetching
$complaintsCount   = ($complaintsResult)   ? $complaintsResult->num_rows   : 0;
$docRequestsCount  = ($docRequestsResult)  ? $docRequestsResult->num_rows  : 0;

// ✅ Tracking number generator (no DB column needed)
function generateTrackingNumber($complaint_id) {
    $prefix = "CMP"; // CMP = Complaint
    $datePart = date("Ymd"); // current date
    $hashPart = strtoupper(substr(md5($complaint_id), 0, 6)); // short unique hash
    return "{$prefix}-{$datePart}-{$hashPart}";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Resident Dashboard</title>
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
        height: auto;
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

    .sidebar ul li a:hover {
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

    /* Dashboard Cards */
    .cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        color: #fff;
        transition: 0.3s;
        position: relative;
        overflow: hidden;
        min-height: 120px;
    }

    .card i {
        font-size: 36px;
        margin-bottom: 10px;
    }

    .card h3 {
        font-size: 20px;
        margin-bottom: 5px;
    }

    .card p {
        font-size: 14px;
        color: #ddd;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }

    .card.complaints { background: #e74c3c; }
    .card.documents { background: #3498db; }
    .card.notifications { background: #f1c40f; color: #333; }

    /* Tables */
    .history-tables {
        display: flex;
        gap: 20px;
        margin-top: 30px;
        flex-wrap: wrap;
    }

    .table-container {
        flex: 1 1 400px;
        background: rgba(255, 255, 255, 0.1);
        padding: 15px;
        border-radius: 10px;
        overflow-x: auto;
    }

    .table-container h3 {
        margin-bottom: 15px;
        text-align: center;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 8px;
        border: 1px solid #ddd;
        text-align: left;
    }

    th {
        background: rgba(74, 144, 226, 0.4);
        color: #fff;
    }

    /* Status Badges */
    .status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: bold;
        color: #fff;
        text-align: center;
        min-width: 80px;
    }

    .status.pending { background: #f39c12; }
    .status.approved { background: #27ae60; }
    .status.rejected { background: #e74c3c; }

    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Barangay Connect</h2><br>
  <img src="../assets/images/bg_logo.png">
  <ul>
    <li><a href="user_dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
    <li><a href="file_complaint.php"><i class="fa-solid fa-plus"></i> File a Complaint</a></li>
    <li><a href="request_document.php"><i class="fa-solid fa-file-circle-plus"></i> Request a Document</a></li>
    <li><a href="transaction_history.php"><i class="fa-solid fa-receipt"></i> Transaction History</a></li>
    <li><a href="user_sms.php"><i class="fa-solid fa-sms"></i> SMS History</a></li>
    <li><a href="account_settings.php"><i class="fa-solid fa-gear"></i> Account Settings</a></li>
    <li><a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <!-- Header -->
  <div class="header">
    <h1>Resident Dashboard</h1>
    <div class="right-section">
      <div class="notification">
        <i class="fa-solid fa-bell"></i>
        <span class="badge">3</span>
      </div>
      <div class="user">
        <i class="fa-solid fa-user-circle"></i>
        <span><?php echo $_SESSION["fullname"] . " / " . $_SESSION["role"]; ?></span>
      </div>
    </div>
  </div>

  <!-- Dashboard Cards -->
  <div class="cards">
    <div class="card complaints">
      <i class="fa-solid fa-comments"></i>
      <h3><?php echo $complaintsCount; ?></h3>
      <p>My Complaints</p>
    </div>
    <div class="card documents">
      <i class="fa-solid fa-file-lines"></i>
      <h3><?php echo $docRequestsCount; ?></h3>
      <p>My Document Requests</p>
    </div>
  </div>

  <!-- History Tables -->
  <div class="history-tables">

    <!-- Complaint History Table -->
    <div class="table-container">
      <h3>Complaint History</h3>
      <table>
        <thead>
          <tr>
            <th>Tracking No.</th>
            <th>Date Filed</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($complaintsResult && $complaintsResult->num_rows > 0): ?>
            <?php while ($row = $complaintsResult->fetch_assoc()): ?>
              <tr>
                <td><?php echo generateTrackingNumber($row['complaint_id']); ?></td>
                <td><?php echo htmlspecialchars($row['filed_date']); ?></td>
                <td>
                  <span class="status <?php echo strtolower($row['status']); ?>">
                    <?php echo htmlspecialchars($row['status']); ?>
                  </span>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="3">No complaints found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Document Request History Table -->
    <div class="table-container">
      <h3>Document Request History</h3>
      <table>
        <thead>
          <tr>
            <th>Request ID</th>
            <th>Document</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($docRequestsResult && $docRequestsResult->num_rows > 0): ?>
            <?php while ($row = $docRequestsResult->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row["request_id"]); ?></td>
                <td><?php echo htmlspecialchars($row["document_type"]); ?></td>
                <td>
                  <span class="status <?php echo strtolower($row["status"]); ?>">
                    <?php echo htmlspecialchars($row["status"]); ?>
                  </span>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="3">No requests found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- SMS History Table -->
    <div class="table-container">
      <h3>SMS History</h3>
      <table>
        <thead>
          <tr>
            <th>SMS ID</th>
            <th>Date</th>
            <th>Message</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>S001</td>
            <td>2025-09-20</td>
            <td>Your document request has been approved.</td>
          </tr>
          <tr>
            <td>S002</td>
            <td>2025-09-22</td>
            <td>Your complaint has been resolved.</td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</div>
</body>
</html>