<?php
session_start();
require_once "../cons/config.php";

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Protect resident access
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Resident") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// 🟢 Fetch data from users + residents_profile
$query = "
    SELECT 
        u.fullname, u.username, u.password_hash, 
        r.*
    FROM users u
    LEFT JOIN residents_profile r ON u.user_id = r.user_id
    WHERE u.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();


// 🟢 Handle account info update
if (isset($_POST["save_account"])) {
    $fullname = trim($_POST["fullname"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET fullname=?, username=?, password_hash=? WHERE user_id=?");
        $stmt->bind_param("ssss", $fullname, $username, $hashed_password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET fullname=?, username=? WHERE user_id=?");
        $stmt->bind_param("sss", $fullname, $username, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION["fullname"] = $fullname;
        echo "<script>alert('Account information updated successfully!'); window.location='account_settings.php';</script>";
        exit;
    }
    $stmt->close();
}


// 🟢 Handle personal info update
if (isset($_POST["save_personal"])) {
    $fields = [
        "first_name", "middle_name", "last_name", "suffix", "sex", "birthdate",
        "civil_status", "nationality", "religion", "address", "livelihood_status",
        "occupation", "educational_attainment", "blood_type", "medical_conditions",
        "allergies", "contact_number", "email_address", "voter_status",
        "pwd_status", "senior_citizen_status", "solo_parent_status"
    ];

    $values = [];
    foreach ($fields as $field) {
        $values[$field] = trim($_POST[$field] ?? '');
    }

    // Check if user already has a profile
    $check = $conn->prepare("SELECT * FROM residents_profile WHERE user_id=?");
    $check->bind_param("s", $user_id);
    $check->execute();
    $existingProfile = $check->get_result()->fetch_assoc();
    $check->close();


    /*** If profile already exists → UPDATE ***/
    if ($existingProfile) {
        // Compare old vs new values to detect changes
        $changes = [];
        foreach ($fields as $field) {
            if ($existingProfile[$field] != $values[$field]) {
                $changes[] = ucfirst(str_replace("_", " ", $field));
            }
        }

        // Proceed with update
        $update = $conn->prepare("
            UPDATE residents_profile SET
                first_name=?, middle_name=?, last_name=?, suffix=?, sex=?, birthdate=?, civil_status=?, 
                nationality=?, religion=?, address=?, livelihood_status=?, occupation=?, educational_attainment=?, 
                blood_type=?, medical_conditions=?, allergies=?, contact_number=?, email_address=?, voter_status=?, 
                pwd_status=?, senior_citizen_status=?, solo_parent_status=?, updated_at=NOW()
            WHERE user_id=?
        ");
        $update->bind_param(
            "sssssssssssssssssssssss",
            $values["first_name"], $values["middle_name"], $values["last_name"], $values["suffix"],
            $values["sex"], $values["birthdate"], $values["civil_status"], $values["nationality"],
            $values["religion"], $values["address"], $values["livelihood_status"], $values["occupation"],
            $values["educational_attainment"], $values["blood_type"], $values["medical_conditions"],
            $values["allergies"], $values["contact_number"], $values["email_address"], $values["voter_status"],
            $values["pwd_status"], $values["senior_citizen_status"], $values["solo_parent_status"], $user_id
        );
        $update->execute();
        $update->close();

        // Log only if there are changes
        if (!empty($changes)) {
            // Generate log_id
            $logRes = $conn->query("
                SELECT log_id 
                FROM activity_logs 
                ORDER BY CAST(SUBSTRING(log_id, 4) AS UNSIGNED) DESC 
                LIMIT 1
            ");
            if ($logRes && $logRow = $logRes->fetch_assoc()) {
                $lastLogNum = (int)substr($logRow["log_id"], 3);
                $newLogNum  = $lastLogNum + 1;
            } else {
                $newLogNum = 1;
            }

            $log_id = "LOG" . str_pad($newLogNum, 6, "0", STR_PAD_LEFT);
            $changedFields = implode(", ", $changes);
            $action = "Resident {$values['first_name']} {$values['last_name']} updated personal information (Changed: $changedFields)";
            $created_at = date("Y-m-d H:i:s");

            $logStmt = $conn->prepare("INSERT INTO activity_logs (log_id, user_id, action, created_at) VALUES (?, ?, ?, ?)");
            $logStmt->bind_param("ssss", $log_id, $user_id, $action, $created_at);
            $logStmt->execute();
            $logStmt->close();
        }

        echo "<script>alert('Personal information updated successfully!'); window.location='account_settings.php';</script>";
        exit;
    }

    /*** If no existing profile → INSERT ***/
    else {
        // Generate new resident_id
        $resResult = $conn->query("
            SELECT resident_id 
            FROM residents_profile 
            ORDER BY CAST(SUBSTRING(resident_id, 2) AS UNSIGNED) DESC 
            LIMIT 1
        ");
        if ($resResult && $resRow = $resResult->fetch_assoc()) {
            $lastResNum = (int)substr($resRow["resident_id"], 1);
            $newResNum  = $lastResNum + 1;
        } else {
            $newResNum = 1;
        }
        $resident_id = "R" . str_pad($newResNum, 6, "0", STR_PAD_LEFT);

        $insert = $conn->prepare("
            INSERT INTO residents_profile (
                resident_id, user_id, first_name, middle_name, last_name, suffix, sex, birthdate, civil_status, 
                nationality, religion, address, livelihood_status, occupation, educational_attainment, blood_type, 
                medical_conditions, allergies, contact_number, email_address, voter_status, pwd_status, 
                senior_citizen_status, solo_parent_status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $insert->bind_param(
            "ssssssssssssssssssssssss",
            $resident_id, $user_id, $values["first_name"], $values["middle_name"], $values["last_name"], 
            $values["suffix"], $values["sex"], $values["birthdate"], $values["civil_status"], $values["nationality"], 
            $values["religion"], $values["address"], $values["livelihood_status"], $values["occupation"], 
            $values["educational_attainment"], $values["blood_type"], $values["medical_conditions"], 
            $values["allergies"], $values["contact_number"], $values["email_address"], $values["voter_status"], 
            $values["pwd_status"], $values["senior_citizen_status"], $values["solo_parent_status"]
        );

        if ($insert->execute()) {
            $insert->close();

            // Generate new log_id
            $logRes = $conn->query("
                SELECT log_id 
                FROM activity_logs 
                ORDER BY CAST(SUBSTRING(log_id, 4) AS UNSIGNED) DESC 
                LIMIT 1
            ");
            if ($logRes && $logRow = $logRes->fetch_assoc()) {
                $lastLogNum = (int)substr($logRow["log_id"], 3);
                $newLogNum  = $lastLogNum + 1;
            } else {
                $newLogNum = 1;
            }

            $log_id = "LOG" . str_pad($newLogNum, 6, "0", STR_PAD_LEFT);
            $action = "Resident profile created for {$values['first_name']} {$values['last_name']} (ID: $resident_id)";
            $created_at = date("Y-m-d H:i:s");

            $logStmt = $conn->prepare("INSERT INTO activity_logs (log_id, user_id, action, created_at) VALUES (?, ?, ?, ?)");
            $logStmt->bind_param("ssss", $log_id, $user_id, $action, $created_at);
            $logStmt->execute();
            $logStmt->close();

            echo "<script>alert('Personal information saved successfully!'); window.location='account_settings.php';</script>";
            exit;
        }
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Barangay Connect | Account Settings</title>
<link rel="icon" href="../assets/images/ghost.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
<?php // Reuse your sidebar & layout styles ?>
*{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
body{background:#FFF8E1;min-height:100vh;display:flex;}
.sidebar{width:250px;background:#343A40;color:#fff;padding:20px;position:fixed;top:0;bottom:0;left:0;box-shadow:2px 0 10px rgba(245,245,245,0.94);}
.sidebar h2{text-align:center;font-size:24px;color:#fff;margin-bottom:15px;}
.sidebar img{display:block;margin:0 auto 20px;max-width:120px;border-radius:50%;border:2px solid rgb(225,234,39);background:rgba(255,255,255,0.1);padding:5px;}
.sidebar ul{list-style:none;}
.sidebar ul li{margin:15px 0;}
.sidebar ul li a{color:#ddd;text-decoration:none;font-size:16px;display:flex;align-items:center;padding:10px;border-radius:6px;transition:0.3s;}
.sidebar ul li a i{margin-right:10px;}
.sidebar ul li a:hover,.sidebar ul li a.active{background:#4a90e2;color:#fff;}
.main-content{position:fixed;top:0;left:250px;right:0;bottom:0;display:flex;flex-direction:column;background:rgba(52,58,64,0.68);color:#fff;padding:20px;overflow-y:auto;}
.header{display:flex;justify-content:space-between;align-items:center;padding-bottom:15px;border-bottom:1px solid rgba(255,255,255,0.2);}
.header h1{font-size:22px;}
.header .user{display:flex;align-items:center;gap:10px;}
.settings-form{background:rgba(255,255,255,0.1);padding:20px;border-radius:12px;max-width:800px;margin:30px auto;width:70%;}
.settings-form h2{text-align:center;margin-bottom:20px;color:#fff;}
.settings-form label{display:block;margin-bottom:5px;font-weight:bold;}
.settings-form input, .settings-form select, .settings-form textarea{
    width:100%;padding:10px;margin-bottom:12px;border-radius:6px;border:none;outline:none;font-size:14px;}
.settings-form button{background:#4a90e2;color:#fff;padding:12px 20px;border:none;border-radius:6px;font-size:16px;cursor:pointer;width:100%;}
.settings-form button:hover{background:#357ABD;}
.section-title{text-align:center;margin:40px 0 20px;font-size:20px;font-weight:bold;}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:15px;}
</style>
</head>
<body>

<div class="sidebar">
  <h2>Barangay Connect</h2><br>
  <img src="../assets/images/bg_logo.png">
  <ul>
    <li><a href="user_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
    <li><a href="file_complaint.php"><i class="fa-solid fa-plus"></i> File a Complaint</a></li>
    <li><a href="request_document.php"><i class="fa-solid fa-file-circle-plus"></i> Request a Document</a></li>
    <li><a href="transaction_history.php"><i class="fa-solid fa-receipt"></i> Transaction History</a></li>
    <li><a href="user_sms.php"><i class="fa-solid fa-sms"></i> SMS History</a></li>
    <li><a href="account_settings.php" class="active"><i class="fa-solid fa-gear"></i> Settings</a></li>
    <li><a href="../logout.php" onclick="return confirm('Are you sure you want to log out?');"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
  </ul>
</div>

<div class="main-content">
  <div class="header">
    <h1>Account Settings</h1>
    <div class="user">
      <i class="fa-solid fa-user-circle"></i>
      <span><?php echo htmlspecialchars($_SESSION["fullname"]); ?> / Resident</span>
    </div>
  </div>

  <div class="settings-form">
  <!-- 🟢 ACCOUNT INFORMATION FORM -->
  <h2>Account Information</h2>
  <form method="POST" action="account_settings.php">
    <label>Full Name</label>
    <input type="text" name="fullname" 
           value="<?php echo htmlspecialchars($user['fullname']); ?>" required>

    <label>Username</label>
    <input type="text" name="username" 
           value="<?php echo htmlspecialchars($user['username']); ?>" required>

    <label>New Password (leave blank to keep current)</label>
    <input type="password" name="password" placeholder="Enter new password">

    <button type="submit" name="save_account">Save Account Info</button>
  </form>

  <!-- Divider -->
  <div class="section-title">Personal Information</div>

  <!-- 🟡 PERSONAL INFORMATION FORM -->
  <form method="POST" action="account_settings.php">
    <div class="grid">

      <div><label>First Name</label>
        <input type="text" name="first_name" 
               value="<?php echo htmlspecialchars($user['first_name']); ?>">
      </div>

      <div><label>Middle Name</label>
        <input type="text" name="middle_name" 
               value="<?php echo htmlspecialchars($user['middle_name']); ?>">
      </div>

      <div><label>Last Name</label>
        <input type="text" name="last_name" 
               value="<?php echo htmlspecialchars($user['last_name']); ?>">
      </div>

      <div><label>Suffix</label>
        <input type="text" name="suffix" 
               value="<?php echo htmlspecialchars($user['suffix']); ?>">
      </div>

      <div>
        <label>Sex</label>
        <select name="sex" required>
          <option value="" disabled <?php echo empty($user['sex']) ? 'selected' : ''; ?>>Select Sex</option>
          <option value="Male" <?php echo ($user['sex'] === 'Male') ? 'selected' : ''; ?>>Male</option>
          <option value="Female" <?php echo ($user['sex'] === 'Female') ? 'selected' : ''; ?>>Female</option>
        </select>
      </div>

      <div>
        <label>Birthdate</label>
        <input type="date" name="birthdate" 
               value="<?php echo htmlspecialchars($user['birthdate']); ?>">
      </div>

      <div>
        <label>Civil Status</label>
        <select name="civil_status" required>
          <option value="" disabled <?php echo empty($user['civil_status']) ? 'selected' : ''; ?>>Select Status</option>
          <?php
            $statuses = ['Single', 'Married', 'Widowed', 'Separated'];
            foreach ($statuses as $status) {
                $selected = ($user['civil_status'] === $status) ? 'selected' : '';
                echo "<option value=\"$status\" $selected>$status</option>";
            }
          ?>
        </select>
      </div>

      <div><label>Nationality</label>
        <input type="text" name="nationality" 
               value="<?php echo htmlspecialchars($user['nationality']); ?>">
      </div>

      <div><label>Religion</label>
        <input type="text" name="religion" 
               value="<?php echo htmlspecialchars($user['religion']); ?>">
      </div>

      <div>
  <label>Address</label>
  <input 
    type="text" 
    name="address" 
    placeholder="House No., Street, Barangay, City/Municipality, Province" 
    value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" 
    required
  >
</div>


      <div>
        <label>Livelihood Status</label>
        <select name="livelihood_status" required>
          <option value="" disabled <?php echo empty($user['livelihood_status']) ? 'selected' : ''; ?>>Select Livelihood Status</option>
          <?php
            $livelihood_options = ['Employed', 'Self-Employed', 'Unemployed', 'Student', 'Retired', 'Others'];
            foreach ($livelihood_options as $option) {
                $selected = ($user['livelihood_status'] === $option) ? 'selected' : '';
                echo "<option value=\"$option\" $selected>$option</option>";
            }
          ?>
        </select>
      </div>

      <div><label>Occupation</label>
        <input type="text" name="occupation" 
               value="<?php echo htmlspecialchars($user['occupation']); ?>">
      </div>

      <div>
        <label>Educational Attainment</label>
        <select name="educational_attainment" required>
          <option value="" disabled <?php echo empty($user['educational_attainment']) ? 'selected' : ''; ?>>Select Educational Attainment</option>
          <?php
            $education_levels = [
              'No Formal Education',
              'Elementary Level',
              'Elementary Graduate',
              'High School Level',
              'High School Graduate',
              'Vocational Course',
              'College Level',
              'College Graduate',
              'Postgraduate / Master’s Degree',
              'Doctorate Degree'
            ];
            foreach ($education_levels as $level) {
                $selected = ($user['educational_attainment'] === $level) ? 'selected' : '';
                echo "<option value=\"$level\" $selected>$level</option>";
            }
          ?>
        </select>
      </div>

      <div>
        <label>Blood Type</label>
        <select name="blood_type">
          <option value="" disabled <?php echo empty($user['blood_type']) ? 'selected' : ''; ?>>Select Blood Type</option>
          <?php
            $blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            foreach ($blood_types as $type) {
                $selected = ($user['blood_type'] === $type) ? 'selected' : '';
                echo "<option value=\"$type\" $selected>$type</option>";
            }
          ?>
        </select>
      </div>

      <div>
        <label>Medical Conditions</label>
        <textarea name="medical_conditions" rows="2"><?php echo htmlspecialchars($user['medical_conditions']); ?></textarea>
      </div>

      <div>
        <label>Allergies</label>
        <textarea name="allergies" rows="2"><?php echo htmlspecialchars($user['allergies']); ?></textarea>
      </div>

      <div>
        <label>Contact Number</label>
        <input type="text" name="contact_number" 
               value="<?php echo htmlspecialchars($user['contact_number']); ?>">
      </div>

      <div>
        <label>Email Address</label>
        <input type="email" name="email_address" 
               value="<?php echo htmlspecialchars($user['email_address']); ?>">
      </div>

      <div>
        <label>Voter Status</label>
        <select name="voter_status">
          <option value="" disabled <?php echo empty($user['voter_status']) ? 'selected' : ''; ?>>Select Voter Status</option>
          <option value="Registered" <?php echo ($user['voter_status'] === 'Registered') ? 'selected' : ''; ?>>Registered</option>
          <option value="Not Registered" <?php echo ($user['voter_status'] === 'Not Registered') ? 'selected' : ''; ?>>Not Registered</option>
        </select>
      </div>

      <div>
        <label>PWD Status</label>
        <select name="pwd_status">
        <option value="" disabled <?php echo empty($user['pwd_status']) ? 'selected' : ''; ?>>Select PWD Status</option>
          <option value="0" <?php echo ($user['pwd_status'] == 0) ? 'selected' : ''; ?>>No</option>
          <option value="1" <?php echo ($user['pwd_status'] == 1) ? 'selected' : ''; ?>>Yes</option>
        </select>
      </div>

      <div>
        <label>Senior Citizen Status</label>
        <select name="senior_citizen_status">
        <option value="" disabled <?php echo empty($user['senior_citizen_status']) ? 'selected' : ''; ?>>Select Senior Citizen Status</option>
          <option value="0" <?php echo ($user['senior_citizen_status'] == 0) ? 'selected' : ''; ?>>No</option>
          <option value="1" <?php echo ($user['senior_citizen_status'] == 1) ? 'selected' : ''; ?>>Yes</option>
        </select>
      </div>

      <div>
        <label>Solo Parent Status</label>
        <select name="solo_parent_status">
        <option value="" disabled <?php echo empty($user['solo_parent_status']) ? 'selected' : ''; ?>>Select Solo Parent Status</option>
          <option value="0" <?php echo ($user['solo_parent_status'] == 0) ? 'selected' : ''; ?>>No</option>
          <option value="1" <?php echo ($user['solo_parent_status'] == 1) ? 'selected' : ''; ?>>Yes</option>
        </select>
      </div>

    </div>

    <button type="submit" name="save_personal">Save Personal Info</button>
  </form>
</div>

</div>

</body>
</html>
