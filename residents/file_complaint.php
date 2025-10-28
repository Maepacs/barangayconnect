<?php
session_start();
require_once "../cons/config.php"; // DB connection

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Protect resident dashboard
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Resident") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Fetch fullname and role
$stmt = $conn->prepare("SELECT fullname, role FROM users WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($fullname, $role);
$stmt->fetch();
$stmt->close();

$_SESSION["fullname"] = $fullname ?? 'Unknown';
$_SESSION["role"] = $role ?? 'Resident';

// Variable to store tracking number for modal
$submitted_tracking_number = null;

// Handle complaint submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $complaint_title = trim($_POST["title"] ?? '');
    $complaint_type  = trim($_POST["category"] ?? '');
    $description     = trim($_POST["description"] ?? '');

    if (empty($complaint_title) || empty($complaint_type) || empty($description)) {
        echo "<script>alert('Please fill all required fields.'); window.history.back();</script>";
        exit;
    }

    // Handle image upload
    $image_file = null;
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "../uploads/complaints/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "gif"];

        if (!in_array($file_type, $allowed_types)) {
            echo "<script>alert('Invalid image file type.'); window.history.back();</script>";
            exit;
        }

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_file = $file_name;
        } else {
            echo "<script>alert('Failed to upload image.'); window.history.back();</script>";
            exit;
        }
    }

    // Generate new complaint ID
    $query = $conn->query("SELECT complaint_id FROM complaints ORDER BY CAST(SUBSTRING(complaint_id, 4) AS UNSIGNED) DESC LIMIT 1");
    if ($query && $query->num_rows > 0) {
        $row = $query->fetch_assoc();
        $num = (int)substr($row['complaint_id'], 3);
        $newId = 'CMP' . str_pad($num + 1, 6, '0', STR_PAD_LEFT);
    } else {
        $newId = 'CMP000001';
    }

// Generate unique tracking number
do {
    // Random 8-character alphanumeric string
    $random_str = strtoupper(bin2hex(random_bytes(4))); // 8 chars
    $tracking_number = "CMP-" . date("Ymd") . "-" . $random_str;

    // Check if it already exists in database
    $checkStmt = $conn->prepare("SELECT tracking_number FROM complaints WHERE tracking_number = ?");
    $checkStmt->bind_param("s", $tracking_number);
    $checkStmt->execute();
    $checkStmt->store_result();
    $exists = $checkStmt->num_rows > 0;
    $checkStmt->close();
} while ($exists);


    // Insert complaint with tracking number
    $stmt = $conn->prepare("
        INSERT INTO complaints 
        (complaint_id, user_id, complaint_title, complaint_type, description, image_file, date_filed, status, handled_by, tracking_number) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?)
    ");
    $stmt->bind_param("sssssssss", $newId, $user_id, $complaint_title, $complaint_type, $description, $image_file, $date_filed, $handled_by, $tracking_number);

    if ($stmt->execute()) {
        // Create resident notification
        $nStmt = $conn->prepare("INSERT INTO notifications (user_id, type, ref_id, title, message) VALUES (?, 'complaint', ?, ?, ?)");
        $notifTitle = 'Complaint Submitted';
        $notifMsg = 'Your complaint "' . $complaint_title . '" was submitted. Tracking: ' . $tracking_number;
        $nStmt->bind_param("ssss", $user_id, $newId, $notifTitle, $notifMsg);
        $nStmt->execute();
        $nStmt->close();

        // Log activity
        $logRes = $conn->query("SELECT log_id FROM activity_logs ORDER BY CAST(SUBSTRING(log_id, 4) AS UNSIGNED) DESC LIMIT 1");
        $newLogNum = ($logRes && $logRes->num_rows > 0)
            ? (int)substr($logRes->fetch_assoc()['log_id'], 3) + 1
            : 1;

        $log_id = "LOG" . str_pad($newLogNum, 6, "0", STR_PAD_LEFT);
        $action = "Resident " . htmlspecialchars($fullname) . " filed a complaint (ID: $newId, Tracking No: $tracking_number)";
        $created_at = date("Y-m-d H:i:s");

        $logStmt = $conn->prepare("INSERT INTO activity_logs (log_id, user_id, action, created_at) VALUES (?, ?, ?, ?)");
        $logStmt->bind_param("ssss", $log_id, $user_id, $action, $created_at);
        $logStmt->execute();
        $logStmt->close();

        // Set variable for modal
        $submitted_tracking_number = $tracking_number;
    } else {
        echo "<script>alert('Error submitting complaint: " . addslashes($stmt->error) . "'); window.history.back();</script>";
        exit;
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Barangay Connect | File Complaint</title>
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

.sidebar ul li a:hover,
.sidebar ul li a.active {
    background: #4a90e2;
    color: #fff;
}

/* Main Content */
.main-content {
    position: fixed;
    top: 0;
    left: 250px;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    background: rgba(52, 58, 64, 0.68);
    color: #fff;
    padding: 20px;
    overflow-y: auto;
}

/* Header */
.header {
    position: sticky;
    top: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    z-index: 10;
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

/* Complaint Form */
.complaint-form {
    background: rgba(255, 255, 255, 0.1);
    padding: 20px;
    border-radius: 12px;
    max-width: 600px;
    margin: 30px auto;
    width: 40%;
}

.complaint-form h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #fff;
}

.complaint-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.complaint-form input[type="text"],
.complaint-form textarea,
.complaint-form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: none;
    outline: none;
    font-size: 14px;
}

.complaint-form textarea {
    resize: vertical;
    min-height: 100px;
}

.complaint-form button {
    background: #e74c3c;
    color: #fff;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s;
    width: 100%;
}

.complaint-form button:hover {
    background: #c0392b;
}

/* Styled File Upload */
.custom-file-upload {
    display: inline-block;
    padding: 12px 20px;
    cursor: pointer;
    border-radius: 6px;
    background: #3498db;
    color: #fff;
    font-weight: bold;
    margin-bottom: 15px;
    text-align: center;
    width: 100%;
    transition: 0.3s;
}

.custom-file-upload i {
    margin-right: 8px;
}

.custom-file-upload:hover {
    background: #2980b9;
}

.custom-file-upload input[type="file"] {
    display: none;
}

/* Image Preview */
.preview-container {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 10px;
}

#imagePreview {
    display: none;
    width: 250px;
    height: 250px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.file-name {
    font-size: 14px;
    color: #555;
    font-weight: bold;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Modal Styles */
.modal {
    display: flex;
    justify-content: center;
    align-items: center;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #f5f5f5;
    color: #000;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    max-width: 400px;
    width: 80%;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.modal-content button {
    margin-top: 15px;
    padding: 10px 20px;
    background-color: #27ae60;
    border: none;
    border-radius: 6px;
    color: #fff;
    cursor: pointer;
}

.modal-content button:hover {
    background-color: #1e8449;
}

  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Barangay Connect</h2><br>
  <img src="../assets/images/bg_logo.png">
  <ul>
    <li><a href="user_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
    <li><a href="file_complaint.php" class="active"><i class="fa-solid fa-plus"></i> File a Complaint</a></li>
    <li><a href="request_document.php"><i class="fa-solid fa-file-circle-plus"></i> Request a Document</a></li>
    <li><a href="transaction_history.php"><i class="fa-solid fa-receipt"></i> Transaction History</a></li>
    <li><a href="user_sms.php"><i class="fa-solid fa-sms"></i> SMS History</a></li>
    <li><a href="account_settings.php"><i class="fa-solid fa-gear"></i> Account Settings</a></li>
    <li><a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="header">
    <h1>File a Complaint</h1>
    <div class="right-section">
      <div class="notification" id="notifBell">
        <i class="fa-solid fa-bell"></i>
        <span class="badge" id="notifBadge"></span>
        <div class="notif-dropdown" id="notifDropdown" style="display:none; position:absolute; right:0; top:28px; background:#1f242b; color:#e8edf3; width:320px; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.45); overflow:hidden; z-index:50;">
          <div class="notif-header" style="padding:10px 12px; background:#e35d2d; color:#fff; font-weight:600;">Notifications
            <button id="markAllReadBtn" style="float:right; background:rgba(0,0,0,0.2); color:#fff; border:1px solid rgba(255,255,255,0.35); padding:2px 8px; border-radius:6px; font-size:12px; cursor:pointer;">Mark all read</button>
          </div>
          <ul class="notif-list" id="notifList" style="list-style:none; max-height:350px; overflow-y:auto; margin:0; padding:0;">
            <li style="padding:10px 12px; border-bottom:1px solid #eee;">Loading...</li>
          </ul>
          <div style="display:flex; justify-content:space-between; padding:8px 10px; background:#15191f; color:#a8b0b9;">
            <button id="prevPage" style="background:#2a3038; color:#e8edf3; border:1px solid #3a424d; padding:4px 8px; border-radius:6px; font-size:12px; cursor:pointer;">Prev</button>
            <span id="pageInfo" style="align-self:center; font-size:12px;">Page 1</span>
            <button id="nextPage" style="background:#2a3038; color:#e8edf3; border:1px solid #3a424d; padding:4px 8px; border-radius:6px; font-size:12px; cursor:pointer;">Next</button>
          </div>
        </div>
      </div>
      <div class="user">
        <i class="fa-solid fa-user-circle"></i>
        <span>
          <?php 
            echo isset($_SESSION["fullname"]) 
              ? htmlspecialchars($_SESSION["fullname"]) . " / " . htmlspecialchars($_SESSION["role"]) 
              : "Guest"; 
          ?>
        </span>
      </div>
    </div>
  </div>

  <!-- Complaint Form -->
  <div class="complaint-form">
    <h2>New Complaint</h2>
    <form action="file_complaint.php" method="POST" enctype="multipart/form-data">
      <label for="title">Complaint Title</label>
      <input type="text" id="title" name="title" placeholder="Enter complaint title" required>

      <label for="category">Category</label>
      <select id="category" name="category" required>
        <option value="" disabled selected>Select category</option>
        <option value="Noise">Noise</option>
        <option value="Traffic">Traffic</option>
        <option value="Sanitation">Sanitation</option>
        <option value="Others">Others</option>
      </select>

      <label for="description">Description</label>
      <textarea id="description" name="description" placeholder="Enter complaint details" required></textarea>

      <label class="custom-file-upload">
        <i class="fa-solid fa-upload"></i> Attach Image
        <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(event)">
      </label>

      <div class="preview-container">
        <img id="imagePreview" src="#" alt="Image Preview">
        <span id="fileName" class="file-name" title=""></span>
      </div><br>
      <button type="submit">Submit Complaint</button>
    </form>
  </div>
  <?php if ($submitted_tracking_number): ?>
  <div id="trackingModal" class="modal">
    <div class="modal-content">
      <h3>Request Submitted Successfully!</h3>
      <p>Your Tracking Number:</p><br>
      <span id="modalTrackingNumber" style="font-family: monospace; font-size: 20px; background:rgb(233, 233, 129); color: #000; padding: 5px 10px; border-radius: 4px;"><?php echo $submitted_tracking_number; ?></span>
      <br>
      <button id="copyBtn">Copy</button>
    </div>
  </div>
<?php endif; ?>




<script>
// Notifications dropdown behavior
const bell = document.getElementById('notifBell');
const dropdown = document.getElementById('notifDropdown');
if (bell) {
  bell.addEventListener('click', function(e){
    e.stopPropagation();
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  });
  document.addEventListener('click', function(){ dropdown.style.display = 'none'; });
}

let currentPage = 1;
const PAGE_SIZE = 8;

async function fetchNotifications(){
  try {
    const res = await fetch(`notifications_api.php?action=list&page=${currentPage}&page_size=${PAGE_SIZE}`);
    const data = await res.json();
    const badge = document.getElementById('notifBadge');
    const list = document.getElementById('notifList');
    if (!badge || !list) return;
    if (!data.success) { badge.textContent = ''; list.innerHTML = '<li style="padding:10px 12px;">'+(data.error||'Failed to load')+'</li>'; return; }
    badge.textContent = data.unread > 0 ? String(data.unread) : '';
    let html = '';
    const item = (icon, title, status, date) => `
      <li style="padding:10px 12px; border-bottom:1px solid #eee;">
        <span style="font-weight:600;"><i class="fa-solid ${icon}"></i> ${title}</span>
        <span style="display:block; color:#666; font-size:12px; margin-top:4px;">Status: ${status} • ${date}</span>
      </li>`;
    (data.items||[]).forEach(n => {
      const icon = n.type==='complaint' ? 'fa-comments' : (n.type==='document' ? 'fa-file-lines' : 'fa-bell');
      html += item(icon, n.title, n.status, n.date);
    });
    list.innerHTML = html || '<li style="padding:10px 12px;">No notifications</li>';

    // pagination state
    const totalPages = Math.max(1, Math.ceil((data.total || 0) / (data.page_size || PAGE_SIZE)));
    if (currentPage > totalPages) currentPage = totalPages;
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    document.getElementById('pageInfo').textContent = `Page ${currentPage}${totalPages ? ' of ' + totalPages : ''}`;
    prevBtn.disabled = currentPage <= 1 || (data.total||0) === 0;
    nextBtn.disabled = currentPage >= totalPages || (data.total||0) === 0;
  } catch (e) { console.error(e); }
}
fetchNotifications();
setInterval(fetchNotifications, 30000);

// pagination controls
document.getElementById('prevPage').addEventListener('click', (e)=>{ e.stopPropagation(); if (e.currentTarget.disabled) return; if (currentPage>1) { currentPage--; fetchNotifications(); }});
document.getElementById('nextPage').addEventListener('click', (e)=>{ e.stopPropagation(); if (e.currentTarget.disabled) return; currentPage++; fetchNotifications(); });

// mark all read
document.getElementById('markAllReadBtn').addEventListener('click', async (e)=>{
  e.stopPropagation();
  const fd = new FormData(); fd.append('action','mark_all_read');
  const res = await fetch('notifications_api.php', { method:'POST', body: fd });
  const j = await res.json();
  if (j.success) fetchNotifications();
});

function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('imagePreview');
    const fileName = document.getElementById('fileName');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        fileName.textContent = file.name;
        fileName.title = file.name;

        if (file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = "block";
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = "none";
        }
    }
}

// Modal copy functionality
document.addEventListener("DOMContentLoaded", function() {
    const modal = document.getElementById('trackingModal');
    if (!modal) return;

    modal.style.display = 'flex';
    const copyBtn = document.getElementById('copyBtn');
    const trackingNumber = document.getElementById('modalTrackingNumber');

    copyBtn.addEventListener('click', () => {
        navigator.clipboard.writeText(trackingNumber.textContent).then(() => {
            alert('Tracking number copied!');
            modal.style.display = 'none';
        });
    });

    // Optional: Close modal if clicked outside content
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.style.display = 'none';
    });
});
</script>

</body>
</html>
