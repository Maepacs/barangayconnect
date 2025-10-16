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

require_once("../cons/config.php"); // DB connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname        = trim($_POST["fullname"]);
    $username         = trim($_POST["username"]);
    $password         = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $role = "Official";


if (empty($fullname) || empty($username) || empty($password) || empty($confirm_password)) {
    die("All fields are required.");
}

    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Hash password
    $password_hash   = password_hash($password, PASSWORD_DEFAULT);

    // Status is always Active
    $status          = "Active";  
    $date_registered = date("Y-m-d H:i:s");

    // Check if username already exists
    $check = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        die("Username already exists.");
    }
    $check->close();

  /** Generate new user_id **/
$firstLetter = strtoupper(substr($fullname, 0, 1));

// Get the latest numeric part globally (not per initial)
$result = $conn->query("
    SELECT user_id 
    FROM users 
    ORDER BY CAST(SUBSTRING(user_id, 3) AS UNSIGNED) DESC 
    LIMIT 1
");

if ($result && $row = $result->fetch_assoc()) {
    // Extract numeric part (after the prefix "U" + first letter)
    $lastIdNum = (int)substr($row["user_id"], 2);
    $newIdNum  = $lastIdNum + 1;
} else {
    // If no users exist at all, start from 2 instead of 1
    $newIdNum = 2;
}

// Pad number to 6 digits
$user_id = "U" . $firstLetter . str_pad($newIdNum, 6, "0", STR_PAD_LEFT);

// Suppose you already validated username/password
$stmt = $conn->prepare("SELECT user_id, fullname, role FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Store in session
    $_SESSION["user_id"]   = $row["user_id"];
    $_SESSION["fullname"]  = $row["fullname"];
    $_SESSION["role"]      = $row["role"];

}

/** Insert into users **/
$stmt = $conn->prepare("INSERT INTO users (user_id, fullname, username, password_hash, role, status, date_registered) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $user_id, $fullname, $username, $password_hash, $role, $status, $date_registered);

if ($stmt->execute()) {
    $stmt->close();


        /** Generate log_id **/
        $logRes = $conn->query("SELECT log_id FROM activity_logs WHERE log_id LIKE 'LOG%' ORDER BY log_id DESC LIMIT 1");
        if ($logRes && $logRow = $logRes->fetch_assoc()) {
            $lastLogNum = (int)substr($logRow["log_id"], 3);
            $newLogNum = $lastLogNum + 1;
        } else {
            $newLogNum = 1;
        }
        $log_id = "LOG" . str_pad($newLogNum, 6, "0", STR_PAD_LEFT);

        /** Insert into activity_logs **/
        $action     = "Created account for $fullname ($role)";
        $created_at = date("Y-m-d H:i:s");
        $logStmt = $conn->prepare("INSERT INTO activity_logs (log_id, user_id, action, created_at) VALUES (?, ?, ?, ?)");
        $logStmt->bind_param("ssss", $log_id, $user_id, $action, $created_at);
        $logStmt->execute();
        $logStmt->close();

        echo "<script>alert('Account created successfully!'); window.location.href='admin_dashboard.php';</script>";
        exit;
    } else {
        die("Error: " . $stmt->error);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Settings</title>
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
  box-shadow: 2px 0 10px rgba(245, 245, 245, 0.94);
  
  /* Added for scrolling */
  overflow-y: auto;     /* Enable vertical scroll */
  overflow-x: hidden;   /* Hide horizontal scroll */
  scrollbar-width: thin; /* For Firefox */
  scrollbar-color: #555 #343A40; /* Thumb and track color */
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

/* Main Content */
.main-content {
  margin-left: 250px;
  flex: 1;
  display: flex;
  flex-direction: column;
  background: rgba(0, 0, 0, 0.55);
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

/* Notification */
.notification {
      position: relative;
      cursor: pointer;
    }

    .notification i {
      font-size: 20px;
      color:rgb(242, 245, 248);
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
    
/* Form */
.create-form {
  margin-top: 30px;
  margin-left: 50px;
  background: rgba(255,255,255,0.08);
  padding: 25px;
  border-radius: 10px;
  max-width: 400px;
}

.create-form h2 {
  color: #FFD54F;
  margin-bottom: 20px;
}

.create-form label {
  display: block;
  margin-bottom: 6px;
  font-weight: bold;
  color: #fff;
}

.input-wrapper {
  position: relative;
  margin-bottom: 20px;
}

.input-wrapper input,
.input-wrapper select {
  width: 100%;
  padding: 10px 40px 10px 12px;
  border: none;
  border-radius: 6px;
  outline: none;
  background: #ccc(146, 220, 237, 0.1);
}

.input-wrapper input::placeholder {
  color: #000000;
}

.input-wrapper .toggle-password {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  cursor: pointer;
  color: #000000;
  font-size: 16px;
}
.input-wrapper select {
  width: 100%;
  padding: 10px 12px;
  border: none;
  border-radius: 6px;
  background: :#ccc(146, 220, 237, 0.1);
  font-size: 15px;
  cursor: pointer;
  appearance: none;
}

.input-wrapper select:focus {
  outline: none;
  background: :#ccc(146, 220, 237, 0.1);
}

.input-wrapper option {
  background:rgba(52, 58, 64, 0.5); /* dark dropdown background */
  color: #ccc(146, 220, 237, 0.1);
}


.create-form button {
  padding: 10px 20px;
  background: #4CAF50; /* green button */
  border: none;
  color: #fff;
  font-weight: bold;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.3s;
}

.create-form button:hover {
  background: #388E3C;
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
      <li><a href="complaints.php"><i class="fa-solid fa-comments"></i> Complaints</a></li>
      <li><a href="residents.php"><i class="fa-solid fa-users"></i> Residents</a></li>
      <li><a href="household.php"><i class="fa-solid fa-people-roof"></i> Household Records</a></li>
      <li><a href="officials.php" class="active"><i class="fa-solid fa-user-shield"></i> Officials</a></li>
      <li><a href="sms_history.php"><i class="fa-solid fa-message"></i> SMS History</a></li>
      <li><a href="activity_logs.php"><i class="fa-solid fa-list-check"></i> Activity Logs</a></li>
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
      <h1>Admin Dashboard</h1>
      <div class="right-section">
        <div class="notification">
          <i class="fa-solid fa-bell"></i>
          <span class="badge">#</span> <!-- Dynamic badge count -->
        </div>
        <div class="user">
          <i class="fa-solid fa-user-circle"></i>
          <span>
            <?php 
             if(isset($_SESSION["fullname"], $_SESSION["role"])) {
              echo htmlspecialchars($_SESSION["fullname"]) . " / " . htmlspecialchars($_SESSION["role"]);
          } else {
              echo "Guest";
          }
          
            ?>
          </span>
        </div>
      </div>
    </div> <!-- ✅ Closed header properly -->


      <!-- Create Personnel Account Form -->
      <form class="create-form" id="createForm" method="POST" action="create_officials.php">
      <h2>Create Personnel Account</h2>

      <label for="fullname">Full Name</label>
      <div class="input-wrapper">
        <input type="text" id="fullname" name="fullname" placeholder="Enter your full name">
        <small style="color: white; font-size: 12px;">
Format: First Name, Middle Name, Last Name, Suffix
</small>
      </div>

      <label for="username">Username</label>
      <div class="input-wrapper">
        <input type="text" id="username" name="username" placeholder="Enter your username">
      </div>

      <label for="password">Password</label>
      <div class="input-wrapper">
        <input type="password" id="password" name="password" placeholder="Enter new password">
        <i class="fa-solid fa-eye toggle-password" data-target="password"></i>
      </div>

      <label for="confirm_password">Confirm Password</label>
      <div class="input-wrapper">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password">
        <i class="fa-solid fa-eye toggle-password" data-target="confirm_password"></i>
      </div>

      <label for="role">Role</label>
      <div class="input-wrapper">
      <select id="role" name="role">
      <option value="Official" selected disabled>Choose Official Role</option>
  <option value="Admin">Admin</option>
  <option value="Barangay Captain">Barangay Captain</option>
  <option value="Barangay Kagawad">Barangay Kagawad</option>
  <option value="Barangay Secretary">Barangay Secretary</option>
  <option value="Barangay Treasurer">Barangay Treasurer</option>
</select>

      </div>

      <button type="submit">Create Account</button>
    </form>

  </div>

  <!-- Validation + Show Password Script -->
  <script>
  document.getElementById("createForm").addEventListener("submit", function(event) {
    const fullName = document.getElementById("fullname").value.trim();
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value.trim();
    const confirmPassword = document.getElementById("confirm_password").value.trim();
    const role = document.getElementById("role").value.trim();

    if (!fullName || !username || !password || !confirmPassword || !role) {
      alert("Please fill in all fields, including Role.");
      event.preventDefault();
      return;
    }

    if (password !== confirmPassword) {
      alert("Passwords do not match. Please try again.");
      event.preventDefault();
      return;
    }

    // ✅ let PHP handle the actual save
    // don’t block form submission if everything is valid
  });

  // Show/Hide Password
  document.querySelectorAll(".toggle-password").forEach(icon => {
    icon.addEventListener("click", function() {
      const targetId = this.getAttribute("data-target");
      const input = document.getElementById(targetId);

      if (input.type === "password") {
        input.type = "text";
        this.classList.remove("fa-eye");
        this.classList.add("fa-eye-slash");
      } else {
        input.type = "password";
        this.classList.remove("fa-eye-slash");
        this.classList.add("fa-eye");
      }
    });
  });

  if (performance.navigation.type === 2) {  
    // Back/forward navigation
    window.location.href = "../login.php"; // ← go up one folder
}

</script>

</body>
</html>
