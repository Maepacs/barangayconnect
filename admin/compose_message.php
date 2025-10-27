<?php
session_start();
require_once "../cons/config.php"; // DB connection

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Protect admin dashboard
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../login.php"); 
    exit;
}

// Function to safely fetch counts
function getCount($conn, $sql) {
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

// Dashboard counts
$complaints = getCount($conn, "SELECT COUNT(*) AS total FROM complaints");
$documents  = getCount($conn, "SELECT COUNT(*) AS total FROM document_request");
$officials  = getCount($conn, "SELECT COUNT(*) AS total FROM barangay_officials");
$residents  = getCount($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'Resident'");

// Fetch latest complaints
$complaintsQuery = $conn->query("
    SELECT c.complaint_id, c.complaint_title, DATE_FORMAT(c.date_filed, '%b %d, %Y') AS date_created,
           u.username AS sender
    FROM complaints c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.status = 'New'
    ORDER BY c.date_filed DESC
");


// Fetch latest document requests
$docsQuery = $conn->query("
    SELECT d.request_id, d.document_type, DATE_FORMAT(d.date_requested, '%b %d, %Y') AS date_requested,
           u.username AS sender
    FROM document_request d
    JOIN users u ON d.user_id = u.user_id
    WHERE d.status = 'Pending'
    ORDER BY d.date_requested DESC
");


// Combine notifications
$notifications = [];


// Complaints notifications
while ($row = $complaintsQuery->fetch_assoc()) {
  $notifications[] = [
      'type'  => 'complaint',
      'id'    => $row['complaint_id'],
      'title' => $row['complaint_title'],
      'date'  => $row['date_created'],
      'sender'=> $row['sender']
  ];
}

// Document requests notifications
while ($row = $docsQuery->fetch_assoc()) {
  $notifications[] = [
      'type'  => 'document',
      'id'    => $row['request_id'],
      'title' => $row['document_type'],
      'date'  => $row['date_requested'],
      'sender'=> $row['sender']
  ];
}


// Total notifications count
$notifCount = count($notifications);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Compose Message</title>
  <link rel="icon" href="../assets/images/BG_logo.png">
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
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(245, 245, 245, 0.94);
  }

  .sidebar h2 {
    text-align: center;
    font-size: 22px;
    color: #FFD54F;
    margin-bottom: 15px;
  }

  .sidebar img {
    display: block;
    margin: 0 auto 20px;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 2px solid #FFD54F;
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

  .sidebar ul li a:hover,
  .sidebar ul li a.active {
    background: #4a90e2;
    color: #fff;
  }

  .sidebar ul li a i {
    margin-right: 10px;
  }

  /* Main Content */
  .main-content {
    margin-left: 260px;
    flex: 1;
    background: rgba(0, 0, 0, 0.55);
    padding: 20px;
    color: #fff;
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
  }

  .right-section {
    display: flex;
    align-items: center;
    gap: 20px;
  }

  /* Notification Bell */
.notification {
  position: relative;
  cursor: pointer;
  color: #fff;
  font-size: 20px;
}

.badge {
  background: red;
  color: white;
  border-radius: 50%;
  padding: 2px 6px;
  font-size: 12px;
  position: absolute;
  top: -8px;
  right: -8px;
  font-weight: bold;
}

/* Dropdown */
.notif-dropdown {
  display: none;
  position: absolute;
  top: 30px;
  right: 0;
  background: #fff;
  color: #333;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  border-radius: 8px;
  width: 320px;
  z-index: 1000;
  max-height: 400px;
  overflow-y: auto;
  animation: fadeIn 0.2s ease-in-out;
}

.notif-dropdown h4 {
  margin: 0;
  padding: 12px;
  font-size: 16px;
  background: #f5f5f5;
  border-bottom: 1px solid #ddd;
}

.notif-dropdown #markAllBtn {
  display: block;
  background: none;
  border: none;
  color: #007bff;
  font-size: 14px;
  text-align: right;
  margin: 0 10px 5px 0;
  cursor: pointer;
}

.notif-dropdown ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.notif-dropdown li {
  padding: 12px;
  border-bottom: 1px solid #eee;
  transition: background 0.2s;
}

.notif-dropdown li:last-child {
  border-bottom: none;
}

.notif-dropdown li a {
  text-decoration: none;
  color: inherit;
  display: block;
}

.notif-dropdown li:hover {
  background: #f9f9f9;
}

/* Different background by type */
.notif-dropdown li.complaint {
  border-left: 4px solid #e74c3c; /* red for complaints */
}

.notif-dropdown li.document {
  border-left: 4px solid #3498db; /* blue for documents */
}

/* Small text */
.notif-dropdown small {
  display: block;
  margin-top: 5px;
  font-size: 12px;
  color: #666;
}

/* Fade animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ✅ Responsive (mobile-friendly) */
@media (max-width: 600px) {
  .notif-dropdown {
    position: fixed;
    top: 60px;   /* below navbar */
    right: 10px;
    left: 10px;
    width: auto;
    max-height: 70vh; /* limit height to avoid full takeover */
    overflow-y: auto;
    border-radius: 12px;
  }

  .notif-dropdown h4 {
    font-size: 18px;
    text-align: center;
  }

  .notif-dropdown li {
    padding: 14px;
    font-size: 15px;
  }

  .notif-dropdown small {
    font-size: 13px;
  }
}


/* Fade animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

  .user {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  /* Message Form */
  .message-form {
    margin-top: 30px;
    margin-left: 50px;
    background: rgba(255,255,255,0.08);
    padding: 25px;
    border-radius: 10px;
    max-width: 700px;
  }

  .message-form h2 {
    color: #FFD54F;
    margin-bottom: 20px;
  }

  .message-form label {
    display: inline-block;
    width: 120px;
    font-weight: bold;
    margin-bottom: 5px;
    color: #fff;
  }

  .message-form input[type="text"],
  .message-form textarea {
    width: calc(100% - 130px);
    padding: 8px;
    border: none;
    border-radius: 6px;
    background: #F5F5F5;
    color: #000;
    margin-bottom: 15px;
  }

  .message-form textarea {
    height: 200px;
    resize: none;
  }

  /* Input Row (From:) */
  .input-row {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
  }

  .input-row label {
    width: 120px;
    font-weight: bold;
    color: #fff;
  }

  .input-row span {
    background: #F5F5F5;
    color: #000;
    padding: 8px 12px;
    border-radius: 6px;
    display: inline-block;
    min-width: 200px;
  }

  /* Buttons */
  .buttons {
    margin-top: 15px;
    margin-left: 120px;
  }

  .buttons input {
    padding: 8px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-right: 10px;
    font-weight: bold;
  }

  .buttons .send {
    background: #4CAF50;
    color: #fff;
  }

  .buttons input:hover {
    opacity: 0.9;
  }
</style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Barangay Connect</h2>
    <img src="../assets/images/bg_logo.png">
    <ul>
      <li><a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
      <li><a href="document_request.php"><i class="fa-solid fa-file-lines"></i> Document Requests</a></li>
      <li><a href="complaints.php"><i class="fa-solid fa-comments"></i> Complaints</a></li>
      <li><a href="residents.php"><i class="fa-solid fa-users"></i> Residents</a></li>
      <li><a href="household.php"><i class="fa-solid fa-people-roof"></i> Household Records</a></li>
      <li><a href="officials.php"><i class="fa-solid fa-user-shield"></i> Officials</a></li>
      <li><a href="compose_message.php" class="active"><i class="fa-solid fa-pen-to-square"></i> Compose Message</a></li>
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
    <!-- Header -->
    <div class="header">
      <h1>Admin Dashboard</h1>
      <div class="right-section">
      <div class="notification" onclick="toggleDropdown()"> 
  <i class="fa-solid fa-bell"></i>
  <span id="notifBadge" class="badge"><?php echo $notifCount; ?></span>

  <!-- Dropdown -->
  <div id="notifDropdown" class="notif-dropdown">
    <h4>Notifications</h4>
    <button id="markAllBtn" onclick="markAllRead(event)">Mark all as read</button>
    <ul id="notifList">
      <?php if(!empty($notifications)): ?>
        <?php foreach($notifications as $notif): ?>
          <li class="<?php echo $notif['type']; ?>">
            <a href="redirect_notification.php?id=<?php echo $notif['id']; ?>" class="notif-link">
              <strong><?php echo ucfirst($notif['type']); ?>:</strong> <?php echo htmlspecialchars($notif['title']); ?>
              <br>
              <small>
  From: <?php echo htmlspecialchars($notif['sender']); ?> | 
  <?php echo htmlspecialchars($notif['date']); ?>
</small>

            </a>
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li>No new notifications</li>
      <?php endif; ?>
    </ul>
  </div>
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
    <form class="message-form">
      <h2>New Message</h2>

      <div class="input-row">
  <label>From:</label>
  <span>BARANGAY CONNECT</span>
</div>
<br>

      <div>
        <label>To:</label>
        <input type="text" placeholder="Enter recipient...">
      </div>

      <div>
        <label>Subject:</label>
        <input type="text" placeholder="Enter subject...">
      </div>

      <div>
        <label style="vertical-align: top;">Message:</label>
        <textarea placeholder="Type your message..."></textarea>
      </div>


      <div class="buttons">
        <input type="submit" value="Send" class="send">
      </div>
    </form>
  </div>

</body>
</html>
