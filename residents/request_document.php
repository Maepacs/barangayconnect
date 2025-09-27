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
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
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
.request-form {
    background: rgba(255, 255, 255, 0.1);
    padding: 25px;
    border-radius: 12px;
    max-width: 600px;
    margin: 30px auto;
}

.request-form h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #fff;
}

.request-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.request-form input[type="text"],
.request-form input[type="email"],
.request-form select,
.request-form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: none;
    outline: none;
    font-size: 14px;
}

.request-form textarea {
    resize: vertical;
    min-height: 100px;
}

.request-form button {
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

.request-form button:hover {
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
#imagePreview {
    display: none;
    margin-top: 10px;
    max-width: 100%;
    border-radius: 6px;
    border: 1px solid #ccc;
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
        <span>Resident</span>
      </div>
    </div>
  </div>

  <!-- Request Form -->
  <div class="request-form">
    <h2>New Document Request</h2>
    <form action="submit_request.php" method="POST" enctype="multipart/form-data">
      <label for="fullname">Full Name</label>
      <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required>

      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" placeholder="Enter your email" required>

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

      <!-- Styled File Upload -->
      <label class="custom-file-upload">
        <i class="fa-solid fa-upload"></i> Attach Supporting Document
        <input type="file" id="supporting" name="supporting" accept="image/*,application/pdf" onchange="previewImage(event)">
      </label>

      <!-- Image Preview -->
      <img id="imagePreview" src="#" alt="File Preview">

      <button type="submit">Submit Request</button>
    </form>
  </div>
</div>

<script>
  function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
      }
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>

</body>
</html>
