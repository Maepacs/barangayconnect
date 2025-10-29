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
  <title>Barangay Connect | Admin Dashboard</title>
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
      background:#FFF8E1;
      min-height: 100vh;
      display: flex;
    }

    /* Sidebar */
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

    .header h1 { font-size: 22px; color: #fff; }

    .header .right-section {
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
  background: #1f242b;
  color: #e8edf3;
  box-shadow: 0 6px 18px rgba(0,0,0,0.45);
  border-radius: 8px;
  width: 320px;
  z-index: 1000;
  overflow: hidden;
}
.notif-dropdown.active { display: block; }
.notif-header { padding: 10px 12px; background:#e35d2d; color:#fff; font-weight:600; }
.notif-list { list-style:none; margin:0; padding:0; max-height:350px; overflow-y:auto; }
.notif-list li { padding: 10px 12px; border-bottom: 1px solid rgba(255,255,255,0.06); }
.notif-list li:last-child { border-bottom:none; }
.notif-item-title { font-weight:600; }
.notif-item-meta { display:block; color:#a8b0b9; font-size:12px; margin-top:4px; }

/* Different background by type */
.notif-list li.complaint { border-left: 4px solid #e74c3c; }
.notif-list li.document { border-left: 4px solid #3498db; }

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
    top: 60px;
    right: 10px;
    left: 10px;
    width: auto;
    max-height: 70vh;
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

    /* User */
    .header .user {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .header .user i { font-size: 20px; color:rgb(233, 237, 241); }

    /* Dashboard cards */
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .card {
      background: rgba(255,255,255,0.1);
      padding: 20px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      gap: 15px;
      transition: 0.3s;
    }

    .card i {
      font-size: 30px;
      color: #ffffff;
      min-width: 40px;
    }

    .card:hover { 
      background: rgba(74, 144, 226, 0.4); 
    }

    .card-info h3 { 
      margin: 0; 
      font-size: 18px; 
    }

    .card-info p { 
      margin: 0; 
      font-size: 14px; 
      color: #ddd; 
    }
  </style>
</head> 
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Barangay Connect</h2><br>
    <img src="../assets/images/bg_logo.png">
    <ul>
      <li><a href="admin_dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
      <li><a href="document_request.php"><i class="fa-solid fa-file-lines"></i> Document Requests</a></li>
      <li><a href="complaints.php"><i class="fa-solid fa-comments"></i> Complaints</a></li>
      <li><a href="residents.php"><i class="fa-solid fa-users"></i> Residents</a></li>
      <li><a href="household.php"><i class="fa-solid fa-people-roof"></i> Household Records</a></li>
      <li><a href="officials.php"><i class="fa-solid fa-user-shield"></i> Officials</a></li>
      <li><a href="compose_message.php"><i class="fa-solid fa-pen-to-square"></i> Compose Message</a></li>
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
    <div class="notif-header">Notifications
      <button id="markAllBtn" style="float:right; background:rgba(0,0,0,0.2); color:#fff; border:1px solid rgba(255,255,255,0.35); padding:2px 8px; border-radius:6px; font-size:12px; cursor:pointer;">Mark all read</button>
    </div>
    <ul id="notifList" class="notif-list">
      <li>Loading...</li>
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

    <!-- ✅ Dashboard Cards -->
    <div class="cards">
      <div class="card">
        <i class="fa-solid fa-comments fa-2x"></i>
        <div class="card-info">
          <h3><?php echo $complaints; ?></h3>
          <p>Total Complaints</p>
        </div>
      </div>
      <div class="card">
        <i class="fa-solid fa-file-lines fa-2x"></i>
        <div class="card-info">
          <h3><?php echo $documents; ?></h3>
          <p>Document Requests</p>
        </div>
      </div>
      <div class="card">
        <i class="fa-solid fa-user-shield fa-2x"></i>
        <div class="card-info">
          <h3><?php echo $officials; ?></h3>
          <p>Barangay Officials</p>
        </div>
      </div>
      <div class="card">
        <i class="fa-solid fa-users fa-2x"></i>
        <div class="card-info">
          <h3><?php echo $residents; ?></h3>
          <p>Registered Residents</p>
        </div>
      </div>
    </div>
  </div>

<!-- Scripts -->
<script>
// Dropdown toggle (resident-style)
function toggleDropdown(){
  const dd = document.getElementById('notifDropdown');
  dd.classList.toggle('active');
}
window.addEventListener('click', (e)=>{
  const isBell = e.target.closest && e.target.closest('.notification');
  if (!isBell) document.getElementById('notifDropdown').classList.remove('active');
});

// Client-side pagination using admin fetch endpoint
let currentPage = 1;
const PAGE_SIZE = 8;
let allItems = [];

function buildItemHTML(item){
  const icon = item.type === 'complaint' ? 'fa-comments' : 'fa-file-lines';
  const read = item.status === 'Read';
  return `<li class="${item.type}" data-id="${item.id}" data-type="${item.type}" data-status="${item.status}">
    <span class="notif-item-title"><i class="fa-solid ${icon}"></i> ${item.type === 'complaint' ? 'Complaint' : 'Document'}: ${item.title}</span>
    <span class="notif-item-meta">Status: ${item.status} • From: ${item.sender} • ${item.date}</span>
    <div class="notif-actions">
      <button class="notif-btn" data-act="mark_read" ${read ? 'disabled' : ''}>Mark read</button>
      <button class="notif-btn" data-act="mark_unread" ${read ? '' : 'disabled'}>Mark unread</button>
    </div>
  </li>`;
}

function renderPage(){
  const list = document.getElementById('notifList');
  const badge = document.getElementById('notifBadge');
  const start = (currentPage - 1) * PAGE_SIZE;
  const pageItems = allItems.slice(start, start + PAGE_SIZE);
  list.innerHTML = pageItems.length ? pageItems.map(buildItemHTML).join('') : '<li>No notifications</li>';
  const unread = allItems.filter(i=>i.status==='Unread').length;
  badge.textContent = unread ? String(unread) : '';
  const totalPages = Math.max(1, Math.ceil(allItems.length / PAGE_SIZE));
  if (currentPage > totalPages) currentPage = totalPages;
  document.getElementById('pageInfo').textContent = `Page ${currentPage}${totalPages ? ' of ' + totalPages : ''}`;
  document.getElementById('prevPage').disabled = currentPage <= 1 || allItems.length === 0;
  document.getElementById('nextPage').disabled = currentPage >= totalPages || allItems.length === 0;
}

async function loadNotifications(){
  try {
    const res = await fetch('fetch_notifications.php');
    const data = await res.json();
    const combined = [];
    (data.complaints || []).forEach(x => combined.push(x));
    (data.documents || []).forEach(x => combined.push(x));
    combined.sort((a,b)=> Date.parse(b.date) - Date.parse(a.date));
    allItems = combined;
    renderPage();
  } catch(err){
    console.error(err);
    document.getElementById('notifList').innerHTML = '<li>Failed to load</li>';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('prevPage').addEventListener('click', (e)=>{ e.stopPropagation(); if (currentPage>1){ currentPage--; renderPage(); } });
  document.getElementById('nextPage').addEventListener('click', (e)=>{ e.stopPropagation(); currentPage++; renderPage(); });
  document.getElementById('markAllBtn').addEventListener('click', async (e)=>{
    e.stopPropagation();
    try {
      const payload = new URLSearchParams();
      payload.append('action','mark_all_read');
      payload.append('items', JSON.stringify(allItems.filter(i=>i.status==='Unread').map(i=>({ id: i.id, type: i.type }))));
      const res = await fetch('notifications_api.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: payload.toString() });
      const j = await res.json();
      if (j.success) { allItems = allItems.map(i=>({ ...i, status:'Read' })); renderPage(); }
    } catch(err) { console.error(err); }
  });
  // item click: if not clicking a button, mark read and redirect
  const listEl = document.getElementById('notifList');
  listEl.addEventListener('click', async (e)=>{
    const btn = e.target.closest('.notif-btn');
    const li = e.target.closest('li[data-id]');
    if (!li) return;
    const id = li.getAttribute('data-id');
    const type = li.getAttribute('data-type');
    if (btn) {
      e.stopPropagation();
      const act = btn.getAttribute('data-act');
      try {
        const payload = new URLSearchParams();
        payload.append('action', act);
        payload.append('id', id);
        payload.append('type', type);
        const res = await fetch('notifications_api.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: payload.toString() });
        const j = await res.json();
        if (j.success) {
          // flip status locally
          allItems = allItems.map(i => (String(i.id)===String(id) && i.type===type) ? { ...i, status: act==='mark_unread' ? 'Unread' : 'Read' } : i);
          renderPage();
        }
      } catch(err){ console.error(err); }
      return;
    }
    // normal item click -> mark read and redirect
    try {
      const payload = new URLSearchParams();
      payload.append('action','mark_read');
      payload.append('id', id);
      payload.append('type', type);
      await fetch('notifications_api.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: payload.toString() });
    } catch(err) { /* ignore */ }
    allItems = allItems.map(i => (String(i.id)===String(id) && i.type===type) ? { ...i, status:'Read' } : i);
    renderPage();
    window.location.href = type === 'complaint' ? 'complaints.php' : 'document_request.php';
  });
  loadNotifications();
  setInterval(loadNotifications, 30000);
});
</script>


</html>
