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

$_SESSION["role"] = $role;
$_SESSION["fullname"] = $fullname;

$role = htmlspecialchars($role);
$fullname = htmlspecialchars($fullname);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $document = isset($_POST["document"]) ? trim($_POST["document"]) : '';
    $purpose  = isset($_POST["purpose"]) ? trim($_POST["purpose"]) : '';

    if (empty($document) || empty($purpose)) {
        echo "<script>alert('Please fill all required fields.'); window.history.back();</script>";
        exit;
    }

    // 🔎 Check duplicate request
    $check = $conn->prepare("SELECT request_id FROM document_request 
                             WHERE user_id = ? AND document_type = ? AND purpose = ? AND status = 'Pending'");
    $check->bind_param("sss", $user_id, $document, $purpose);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('You already submitted this document request and it is still pending.'); window.history.back();</script>";
        $check->close();
        exit;
    }
    $check->close();

    // File upload
    $supporting_file = null;
    if (!empty($_FILES["supporting"]["name"])) {
        $target_dir = "../uploads/documents/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . "_" . basename($_FILES["supporting"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "gif", "pdf"];

        if (!in_array($file_type, $allowed_types)) {
            echo "<script>alert('Invalid file type. Allowed: JPG, JPEG, PNG, GIF, PDF.'); window.history.back();</script>";
            exit;
        }

        if (!move_uploaded_file($_FILES["supporting"]["tmp_name"], $target_file)) {
            echo "<script>alert('Failed to upload file.'); window.history.back();</script>";
            exit;
        }

        $supporting_file = $file_name;
    }

    // Generate request ID
    $query = $conn->query("SELECT request_id FROM document_request ORDER BY CAST(SUBSTRING(request_id, 2) AS UNSIGNED) DESC LIMIT 1");
    if ($query->num_rows > 0) {
        $row = $query->fetch_assoc();
        $num = (int)substr($row['request_id'], 1);
        $newId = 'D' . str_pad($num + 1, 6, '0', STR_PAD_LEFT);
    } else {
        $newId = 'D000001';
    }

    $date_request = date("Y-m-d H:i:s");

    // Insert request
    $stmt = $conn->prepare("INSERT INTO document_request 
        (request_id, user_id, document_type, purpose, supporting_file, status, date_requested) 
        VALUES (?, ?, ?, ?, ?, 'Pending', ?)");
    $stmt->bind_param("ssssss", $newId, $user_id, $document, $purpose, $supporting_file, $date_request);

    if ($stmt->execute()) {
        // Generate log ID
        $logRes = $conn->query("SELECT log_id FROM activity_logs ORDER BY CAST(SUBSTRING(log_id, 4) AS UNSIGNED) DESC LIMIT 1");
        if ($logRes && $logRes->num_rows > 0) {
            $logRow = $logRes->fetch_assoc();
            $newLogNum = (int)substr($logRow["log_id"], 3) + 1;
        } else {
            $newLogNum = 1;
        }

        $log_id = "LOG" . str_pad($newLogNum, 6, "0", STR_PAD_LEFT);
        $action = "Resident $fullname requested a document (ID: $newId)";
        $created_at = date("Y-m-d H:i:s");

        $logStmt = $conn->prepare("INSERT INTO activity_logs (log_id, user_id, action, created_at) VALUES (?, ?, ?, ?)");
        $logStmt->bind_param("ssss", $log_id, $user_id, $action, $created_at);
        $logStmt->execute();
        $logStmt->close();

        echo "<script>alert('Document request submitted successfully!'); window.location='request_document.php';</script>";

    } else {
        echo "<script>alert('Error saving request: " . addslashes($stmt->error) . "'); window.history.back();</script>";
    }

    $stmt->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Request Document</title>
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

/* Document Request Form */
/* Request Form Container */
.request-form {
  background: rgba(255, 255, 255, 0.12);
  padding: 25px;
  border-radius: 12px;
  max-width: 600px;
  margin: 30px auto;
  box-shadow: 0 4px 12px rgba(0,0,0,0.25);
  width: 40%;
}

/* Form Heading */
.request-form h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #fff;
  font-size: 20px;
}

/* Labels */
.request-form label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  color: #fff;
}

/* Inputs / Select / Textarea */
.request-form input[type="text"],
.request-form input[type="email"],
.request-form select,
.request-form textarea {
  width: 100%;
  padding: 12px;
  margin-bottom: 18px;
  border-radius: 6px;
  border: 1px solid #ccc;
  outline: none;
  font-size: 14px;
  background: #fff;
  color: #333;
}

.request-form textarea {
  resize: vertical;
  min-height: 100px;
}

/* Submit Button */
.request-form button {
  background: #e74c3c;
  color: #fff;
  padding: 12px 20px;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
  width: 100%;
}

.request-form button:hover {
  background: #c0392b;
}

/* Custom File Upload Button */
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

/* File Preview Container */
/* File Preview Container (hidden by default) */
.preview-container {
  display: none;   /* hide container until file is uploaded */
  align-items: center;
  gap: 15px;
  margin-top: 15px;
  padding: 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
  background: rgba(255, 255, 255, 0.08);
}

/* Image Preview */
.preview-container img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #999;
  background: #f4f4f4;
}

/* Filename */
.preview-container .file-name {
  font-size: 14px;
  color: #fff;
  word-break: break-word;
  max-width: 380px;
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
    <li><a href="request_document.php" class="active"><i class="fa-solid fa-file-circle-plus"></i> Request a Document</a></li>
    <li><a href="transaction_history.php"><i class="fa-solid fa-receipt"></i> Transaction History</a></li>
    <li><a href="user_sms.php"><i class="fa-solid fa-sms"></i> SMS History</a></li>
    <li><a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
    <li><a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
  </ul>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="header">
    <h1>Request Document</h1>
    <div class="right-section">
      <div class="notification">
        <i class="fa-solid fa-bell"></i>
        <span class="badge">3</span>
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

  <!-- Request Form -->
  <div class="request-form">
    <h2>New Document Request</h2>
    <form action="request_document.php" method="POST" enctype="multipart/form-data">
      <label for="document">Document Type</label>
      <select id="document" name="document" required>
        <option value="" disabled selected>Select document</option>
        <option value="Barangay Clearance">Barangay Clearance</option>
        <option value="Certificate of Residency">Certificate of Residency</option>
        <option value="Indigency Certificate">Indigency Certificate</option>
        <option value="Others">Others</option>
      </select>

      <label for="purpose">Purpose</label>
      <textarea id="purpose" name="purpose" placeholder="State the purpose of the document" required></textarea>

      <label class="custom-file-upload">
  <i class="fa-solid fa-upload"></i> Attach Supporting File
  <input type="file" id="supporting" name="supporting" accept="image/*,application/pdf" onchange="previewImage(event)">
</label>

<!-- Preview + Filename -->
<div class="preview-container">
  <img id="imagePreview" src="#" alt="Image Preview">
  <span id="fileName" class="file-name" title=""></span>
</div><br>

<button type="submit">Submit Request</button>


    </form>
  </div>
</div>

<script>
function previewImage(event) {
  const input = event.target;
  const previewContainer = document.querySelector('.preview-container');
  const preview = document.getElementById('imagePreview');
  const fileName = document.getElementById('fileName');
  
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const ext = file.name.split('.').pop().toLowerCase();

    // Show filename
    fileName.textContent = file.name;
    fileName.title = file.name;

    // Show preview container
    previewContainer.style.display = 'flex';

    if (ext === 'pdf') {
      // Hide image preview for PDFs
      preview.style.display = 'none';
    } else {
      // Show image preview
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
      }
      reader.readAsDataURL(file);
    }
  }
}

</script>


</body>
</html>
