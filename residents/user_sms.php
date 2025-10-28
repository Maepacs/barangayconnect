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

// Fetch fullname and role from users table
$stmt = $conn->prepare("SELECT fullname, role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | SMS History</title>
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

/* SMS Table */
.sms-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    overflow: hidden;
}

.sms-table th,
.sms-table td {
    padding: 12px 15px;
    text-align: left;
}

.sms-table th {
    background: #4a90e2;
    color: #fff;
}

.sms-table tr:nth-child(even) {
    background: rgba(255, 255, 255, 0.05);
}

.sms-table tr:hover {
    background: rgba(255, 255, 255, 0.2);
    cursor: pointer;
}

.status {
    padding: 5px 10px;
    border-radius: 6px;
    font-weight: bold;
    text-align: center;
}

.status.sent {
    background: #27ae60;
    color: #fff;
}

.status.failed {
    background: #c0392b;
    color: #fff;
}

.status.pending {
    background: #f39c12;
    color: #fff;
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
    <li><a href="file_complaint.php"><i class="fa-solid fa-plus"></i> File a Complaint</a></li>
    <li><a href="request_document.php"><i class="fa-solid fa-file-circle-plus"></i> Request a Document</a></li>
    <li><a href="transaction_history.php"><i class="fa-solid fa-receipt"></i> Transaction History</a></li>
    <li><a href="user_sms.php" class="active"><i class="fa-solid fa-sms"></i> SMS History</a></li>
    <li><a href="account_settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
    <li><a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="header">
    <h1>SMS History</h1>
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
      echo isset($_SESSION["fullname"]) ? $_SESSION["fullname"] . " / " . $_SESSION["role"] : "Guest"; 
    ?>
  </span>
</div>
      </div>
    </div> <!-- ✅ Closed header properly -->


  <!-- SMS Table -->
  <table class="sms-table">
    <thead>
      <tr>
        <th>Date</th>
        <th>Sender</th>
        <th>Message</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>2025-09-20 09:15</td>
        <td>Barangay Connect</td>
        <td>Your document request has been approved.</td>
        <td><span class="status sent">Sent</span></td>
      </tr>
      <tr>
        <td>2025-09-21 14:40</td>
        <td>Barangay Connect</td>
        <td>Reminder: File your complaint before the deadline.</td>
        <td><span class="status sent">Sent</span></td>
      </tr>
      <tr>
        <td>2025-09-22 08:30</td>
        <td>Barangay Connect</td>
        <td>SMS delivery failed. Please check your number.</td>
        <td><span class="status failed">Failed</span></td>
      </tr>
      <!-- Add more rows dynamically from database -->
    </tbody>
  </table>
</div>

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
      <li style=\"padding:10px 12px; border-bottom:1px solid rgba(255,255,255,0.06);\">\n        <span style=\"font-weight:600;\"><i class=\"fa-solid ${icon}\"></i> ${title}</span>\n        <span style=\"display:block; color:#a8b0b9; font-size:12px; margin-top:4px;\">Status: ${status} • ${date}</span>\n      </li>`;
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
    const pageInfo = document.getElementById('pageInfo');
    if (prevBtn && nextBtn && pageInfo) {
      pageInfo.textContent = `Page ${currentPage}${totalPages ? ' of ' + totalPages : ''}`;
      prevBtn.disabled = currentPage <= 1 || (data.total||0) === 0;
      nextBtn.disabled = currentPage >= totalPages || (data.total||0) === 0;
    }
  } catch (e) { console.error(e); }
}
fetchNotifications();
setInterval(fetchNotifications, 30000);

// pagination controls
const prevEl = document.getElementById('prevPage');
const nextEl = document.getElementById('nextPage');
if (prevEl && nextEl) {
  prevEl.addEventListener('click', (e)=>{ e.stopPropagation(); if (e.currentTarget.disabled) return; if (currentPage>1) { currentPage--; fetchNotifications(); }});
  nextEl.addEventListener('click', (e)=>{ e.stopPropagation(); if (e.currentTarget.disabled) return; currentPage++; fetchNotifications(); });
}

// mark all read
const markAllBtn = document.getElementById('markAllReadBtn');
if (markAllBtn) {
  markAllBtn.addEventListener('click', async (e)=>{
    e.stopPropagation();
    const fd = new FormData(); fd.append('action','mark_all_read');
    const res = await fetch('notifications_api.php', { method:'POST', body: fd });
    const j = await res.json();
    if (j.success) fetchNotifications();
  });
}
</script>

</body>
</html>
