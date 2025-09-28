<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Barangay Connect | Activity Logs</title>
<link rel="icon" href="../assets/images/ghost.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
* { box-sizing: border-box; margin:0; padding:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
body { background:#FFF8E1; min-height:100vh; display:flex; color:#fff;}
.sidebar { width:250px; background:#343A40; color:#fff; padding:20px; position:fixed; top:0; bottom:0; left:0; box-shadow:2px 0 10px rgba(245,245,245,0.94);}
.sidebar h2{text-align:center; font-size:22px; margin-bottom:15px;}
.sidebar img{display:block; margin:0 auto 20px; max-width:120px; border-radius:50%; border:2px solid rgb(225,234,39); background: rgba(255,255,255,0.1); padding:5px;}
.sidebar ul{list-style:none;}
.sidebar ul li{margin:15px 0;}
.sidebar ul li a{color:#ddd; text-decoration:none; font-size:16px; display:flex; align-items:center; padding:10px; border-radius:6px; transition:0.3s;}
.sidebar ul li a i{margin-right:10px;}
.sidebar ul li a:hover, .sidebar ul li a.active{background:#4a90e2; color:#fff;}
.main-content {margin-left:250px; flex:1; display:flex; flex-direction:column; background:rgba(0,0,0,0.55); padding:20px;}
.header {display:flex; justify-content:space-between; align-items:center; padding-bottom:15px; border-bottom:1px solid rgba(255,255,255,0.2);}
.header h1{font-size:22px;}
.header .right-section{display:flex; align-items:center; gap:20px;}
.header .user{display:flex; align-items:center; gap:10px;}
.header .user i{font-size:20px;}
.search-filter {display:flex; justify-content:flex-end; gap:10px; margin:20px 0;}
.search-filter input {padding:8px 12px; border-radius:6px 0 0 6px; border:none; outline:none; width:200px;}
.search-filter select {padding:8px 12px; border-radius:0 6px 6px 0; border:none; outline:none; background:#4a90e2; color:#fff; cursor:pointer;}
.table-container {background: rgba(255,255,255,0.08); padding:20px; border-radius:10px; overflow-x:auto; margin-bottom:20px;}
table {width:100%; border-collapse:collapse;}
th, td {padding:12px 15px; border-bottom:1px solid rgba(255,255,255,0.2); text-align:left; font-size:14px;}
th {background:rgba(74,144,226,0.6); font-weight:bold;}
tr:hover{background:rgba(255,255,255,0.1);}
.action-btn {padding:6px 10px; margin:2px; border:none; border-radius:6px; cursor:pointer; font-size:12px;}
.action-btn.view {background:#3498DB; color:#fff;}
.action-btn.delete {background:#E74C3C; color:#fff;}
.section-title{margin:15px 0; font-size:18px; color:#FFD54F;}
.pagination {display:flex; justify-content:center; gap:10px; margin-top:10px;}
.pagination button {padding:6px 12px; border:none; border-radius:6px; cursor:pointer; background:#4a90e2; color:#fff;}
.pagination button.disabled {opacity:0.5; cursor:not-allowed;}
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
      <li><a href="complaints.php"><i class="fa-solid fa-comments"></i> Complaints</a></li>
      <li><a href="residents.php"><i class="fa-solid fa-users"></i> Residents</a></li>
      <li><a href="#"><i class="fa-solid fa-user-shield"></i> Officials</a></li>
      <li><a href="create_official.php"><i class="fas fa-user-plus"></i> Create Official Account</a></li>
      <li><a href="sms_history.php"><i class="fa-solid fa-message"></i> SMS History</a></li>
      <li><a href="activity_logs.php" class="active"><i class="fa-solid fa-list-check"></i> Activity Logs</a></li>
      <li><a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
      <li><a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');">
        <i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      </li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="header">
      <h1>Activity Logs</h1>
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

    <!-- Search & Filter -->
    <div class="search-filter">
      <input type="text" id="searchInput" placeholder="Search logs...">
      <select id="actionFilter">
        <option value="">All Actions</option>
        <option value="Logged in">Logged in</option>
        <option value="Deleted">Deleted</option>
        <option value="Updated">Updated</option>
        <option value="Sent SMS">Sent SMS</option>
      </select>
    </div>

    <!-- Logs Table -->
    <h2 class="section-title">User Activities</h2>
    <div class="table-container">
      <table id="logsTable">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Action</th>
            <th>Date & Time</th>
            <th>IP Address</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Sample Logs -->
          <tr><td></td><td>Admin</td><td>Logged in</td><td>2025-09-25 08:15 AM</td><td>192.168.1.10</td><td><button class="action-btn view">View</button><button class="action-btn delete">Delete</button></td></tr>
          <tr><td></td><td>Admin</td><td>Deleted</td><td>2025-09-24 02:45 PM</td><td>192.168.1.10</td><td><button class="action-btn view">View</button><button class="action-btn delete">Delete</button></td></tr>
          <tr><td></td><td>Admin</td><td>Sent SMS</td><td>2025-09-23 09:10 AM</td><td>192.168.1.10</td><td><button class="action-btn view">View</button><button class="action-btn delete">Delete</button></td></tr>
          <tr><td></td><td>Admin</td><td>Updated</td><td>2025-09-22 11:20 AM</td><td>192.168.1.10</td><td><button class="action-btn view">View</button><button class="action-btn delete">Delete</button></td></tr>
          <tr><td></td><td>Admin</td><td>Logged in</td><td>2025-09-21 07:50 AM</td><td>192.168.1.10</td><td><button class="action-btn view">View</button><button class="action-btn delete">Delete</button></td></tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination" id="pagination"></div>
  </div>

<script>
let logsPerPage = 3;
let currentPage = 1;

function assignLogIDs(filteredRows) {
  filteredRows.forEach((row, index) => {
    row.querySelector("td:first-child").textContent = "L" + (index + 1 + (currentPage-1)*logsPerPage);
  });
}

function renderTable() {
  const filterValue = document.getElementById('actionFilter').value.toLowerCase();
  const searchValue = document.getElementById('searchInput').value.toLowerCase();
  const allRows = Array.from(document.querySelectorAll('#logsTable tbody tr'));

  let filteredRows = allRows.filter(row => {
    const actionText = row.cells[2].textContent.toLowerCase();
    const rowText = row.innerText.toLowerCase();
    return (actionText.includes(filterValue) || filterValue === "") && rowText.includes(searchValue);
  });

  allRows.forEach(row => row.style.display = 'none');

  const totalPages = Math.ceil(filteredRows.length / logsPerPage);
  const start = (currentPage - 1) * logsPerPage;
  const end = start + logsPerPage;
  const pageRows = filteredRows.slice(start, end);
  pageRows.forEach(row => row.style.display = '');

  assignLogIDs(filteredRows);
  renderPagination(totalPages);
}

function renderPagination(totalPages) {
  const pagination = document.getElementById('pagination');
  pagination.innerHTML = '';
  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement('button');
    btn.textContent = i;
    btn.classList.toggle('disabled', i === currentPage);
    btn.addEventListener('click', () => { currentPage = i; renderTable(); });
    pagination.appendChild(btn);
  }
}

document.getElementById('searchInput').addEventListener('keyup', () => { currentPage = 1; renderTable(); });
document.getElementById('actionFilter').addEventListener('change', () => { currentPage = 1; renderTable(); });

document.querySelectorAll(".delete").forEach(btn => {
  btn.addEventListener("click", () => {
    if (confirm("Are you sure you want to delete this log?")) {
      btn.closest("tr").remove();
      renderTable();
    }
  });
});

document.querySelectorAll(".view").forEach(btn => {
  btn.addEventListener("click", () => {
    const row = btn.closest("tr");
    alert(`User: ${row.cells[1].textContent}\nAction: ${row.cells[2].textContent}\nDate & Time: ${row.cells[3].textContent}\nIP: ${row.cells[4].textContent}`);
  });
});

renderTable();
</script>
</body>
</html>
