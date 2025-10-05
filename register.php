<?php
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once("../barangayconnect/cons/config.php"); // DB connection

$message = "";

// ✅ Handle AJAX username check
if (isset($_POST['check_username'])) {
    $username = trim($_POST['check_username']);

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    echo json_encode([
        "status" => $stmt->num_rows > 0 ? "taken" : "available"
    ]);

    $stmt->close();
    exit; 
}

// ✅ Registration process
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['check_username'])) {
    $fullname        = trim($_POST["fullName"] ?? ''); // match HTML field
    $username        = trim($_POST["username"] ?? '');
    $password        = trim($_POST["password"] ?? '');
    $confirmPassword = trim($_POST["confirmPassword"] ?? ''); // match HTML field

    // Validation
    if (empty($fullname) || empty($username) || empty($password) || empty($confirmPassword)) {
        $message = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
    } else {
        // Check if username already exists
        $check = $conn->prepare("SELECT username FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Username already exists.";
        } else {
            $password_hash   = password_hash($password, PASSWORD_DEFAULT);
            $role            = "Resident";  
            $status          = "Active";
            $date_registered = date("Y-m-d H:i:s");

          /** Generate new user_id **/
$firstLetter = strtoupper(substr($fullname, 0, 1));

$result = $conn->query("SELECT user_id 
                        FROM users 
                        ORDER BY CAST(SUBSTRING(user_id, 2) AS UNSIGNED) DESC 
                        LIMIT 1");

if ($result && $row = $result->fetch_assoc()) {
    $lastIdNum = (int)substr($row["user_id"], 2); 
    $newIdNum  = $lastIdNum + 1;
} else {
    $newIdNum = 2; // 👈 first ever user starts at 2
}

$user_id = "U" . $firstLetter . str_pad($newIdNum, 6, "0", STR_PAD_LEFT);


            /** Insert into users **/
            $stmt = $conn->prepare("INSERT INTO users (user_id, fullname, username, password_hash, role, status, date_registered) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $user_id, $fullname, $username, $password_hash, $role, $status, $date_registered);

            if ($stmt->execute()) {
                $stmt->close();

             /** Generate log_id **/
$logRes = $conn->query("SELECT log_id FROM activity_logs 
ORDER BY CAST(SUBSTRING(log_id, 4) AS UNSIGNED) DESC 
LIMIT 1");

if ($logRes && $logRow = $logRes->fetch_assoc()) {
$lastLogNum = (int)substr($logRow["log_id"], 3); // skip 'LOG'
$newLogNum  = $lastLogNum + 1;
} else {
$newLogNum = 2; // 👈 first log starts at 2
}

$log_id = "LOG" . str_pad($newLogNum, 6, "0", STR_PAD_LEFT);

/** Insert into activity_logs **/
$action     = "Resident $fullname registered an account";
$created_at = date("Y-m-d H:i:s");

$logStmt = $conn->prepare("INSERT INTO activity_logs (log_id, user_id, action, created_at) VALUES (?, ?, ?, ?)");
$logStmt->bind_param("ssss", $log_id, $user_id, $action, $created_at);
$logStmt->execute();
$logStmt->close();


                $message = "Registration successful!";
            } else {
                $message = "Error: " . $stmt->error;
            }
        }
        $check->close();
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> Barangay Connect | Register </title>
  <link rel="icon" href="assets/images/ghost.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: url("assets/images/bg.png") no-repeat center center;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
}


.container {
  position: relative;
  border-radius: 10px;
  overflow: hidden; /* so blur doesn’t spill out */
  width: 450%;
  max-width: 400px;
  padding: 30px;
  margin: auto; /* centers horizontally if needed */
  box-shadow: 0 5px 15px rgb(245, 245, 245);
  background: rgba(0, 0, 0, 0.55); /* optional semi-transparent */
}


.container::before {
  content: "";
  position: absolute;
  inset: 0;
  background-size: cover;
  filter: blur(5px); /* ✅ removed "controls blur level" */
  z-index: 0;
}


/* Foreground content (form) */
.container form,
.container h1 {
  position: relative;
  z-index: 1;
}

.container {
  color: #000000; /* ensure text stays readable */
}


h1 {
  text-align: center;
  margin-bottom: 10px;
  color: #ffffff;
  font-size: 26px;
}

h1 span {
  color: #4a90e2; /* highlight Barangay Connect */
}

.subtitle {
  text-align: center;
  margin-bottom: 20px;
  font-size: 16px;
  color: #dddddd;
}

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
      color: #ffffff;
    }

    input {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      transition: border 0.3s, box-shadow 0.3s;
    }

    input:focus {
      outline: none;
      border-color: #4a90e2;
      box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
    }

    .password-container {
      position: relative;
    }

    .input-container,
.password-container {
  position: relative;
}

.input-container i,
.password-container i.fa-lock {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #777;
}

.input-container input,
.password-container input {
  padding-left: 35px; /* space for icon */
}

.password-container .toggle-password {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #777;
  font-size: 18px;
}


    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: #777;
      font-size: 18px;
    }

    .error-message,
    .success-message {
      font-size: 14px;
      margin-top: 5px;
      display: none;
    }

    .error-message {
      color: #e74c3c;
    }

    .success-message {
      color: #2ecc71;
    }

    button[type="submit"] {
      width: 100%;
      padding: 12px;
      background-color: #4a90e2;
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button[type="submit"]:hover {
      background-color: #357ae8;
    }

    .form-footer {
      text-align: center;
      margin-top: 20px;
      color: #777;
    }

    .form-footer a {
      color: #4a90e2;
      text-decoration: none;
    }

    .form-footer a:hover {
      text-decoration: underline;
    }

    .input-error {
      border-color:rgb(207, 32, 12);

    }

    .input-success {
      border-color: #2ecc71;
    }
  </style>
</head>
<body>
<div class="container">
    <h1>Welcome to <br><span>Barangay Connect</span></h1>
    <p class="subtitle">Please create your account</p>
    <form id="registrationForm" method="POST" action="register.php" novalidate>
      <div class="form-group">
        <div class="input-container">
          <i class="fa-solid fa-user"></i>
          <label for="fullName">Full Name</label>
<input type="text" id="fullName" name="fullName" placeholder="Enter your Full Name">
<br>
<small style="color: gray; font-size: 12px;">
Format: First Name, Middle Name, Last Name, Suffix
</small>

        </div>
        <div class="error-message" id="fullNameError">Full name must be at least 2 characters long</div>
      </div>

      <div class="form-group">
        <label for="username">Username</label>
        <div class="input-container">
          <i class="fa-solid fa-id-card"></i>
          <input type="text" id="username" name="username" placeholder="Create username">
        </div>
        <div class="error-message" id="usernameError">Username must be 3–20 characters (letters, numbers, underscores)</div>
        <div class="success-message" id="usernameSuccess">Username is available</div>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="password-container">
          <i class="fa-solid fa-lock"></i>
          <input type="password" id="password" name="password" placeholder="Create a password">
          <button type="button" class="toggle-password" id="togglePassword">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
        <div class="error-message" id="passwordError">Password must be at least 8 characters with uppercase, lowercase, number, and special character</div>
      </div>

      <div class="form-group">
        <label for="confirmPassword">Confirm Password</label>
        <div class="password-container">
          <i class="fa-solid fa-lock"></i>
          <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password">
          <button type="button" class="toggle-password" id="toggleConfirmPassword">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
        <div class="error-message" id="confirmPasswordError">Passwords do not match</div>
      </div>

      <button type="submit">Register</button>

      <div class="form-footer">
        Already have an account? <a href="login.php">Sign in</a>
      </div>
    </form>
  </div>


<script>

<?php if (!empty($message)) : ?>
document.addEventListener("DOMContentLoaded", () => {
  alert("<?= $message ?>");
  <?php if ($message === "Registration successful!") : ?>
    window.location.href = "login.php";
  <?php endif; ?>
});
<?php endif; ?>


document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('registrationForm');
  const fullNameInput = document.getElementById('fullName');
  const usernameInput = document.getElementById('username');
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirmPassword');
  const togglePasswordBtn = document.getElementById('togglePassword');
  const toggleConfirmPasswordBtn = document.getElementById('toggleConfirmPassword');

  // ✅ Toggle password visibility
  function togglePassword(input, button) {
    const icon = button.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
  }
  togglePasswordBtn.addEventListener('click', () => togglePassword(passwordInput, togglePasswordBtn));
  toggleConfirmPasswordBtn.addEventListener('click', () => togglePassword(confirmPasswordInput, toggleConfirmPasswordBtn));

  // ✅ Helper for validation UI
  function setValidation(input, isValid, errorId, successId = null) {
    const errorEl = document.getElementById(errorId);
    const successEl = successId ? document.getElementById(successId) : null;

    if (isValid) {
      input.classList.remove('input-error');
      input.classList.add('input-success');
      errorEl.style.display = 'none';
      if (successEl) successEl.style.display = 'block';
    } else {
      input.classList.add('input-error');
      input.classList.remove('input-success');
      errorEl.style.display = 'block';
      if (successEl) successEl.style.display = 'none';
    }
    return isValid;
  }

  // ✅ Individual validations
  function validateFullName() {
    return setValidation(fullNameInput, fullNameInput.value.trim().length >= 2, 'fullNameError');
  }

  function validatePassword() {
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;
    return setValidation(passwordInput, regex.test(passwordInput.value), 'passwordError');
  }

  function validateConfirmPassword() {
    const isValid = confirmPasswordInput.value === passwordInput.value && confirmPasswordInput.value !== '';
    return setValidation(confirmPasswordInput, isValid, 'confirmPasswordError');
  }

  // ✅ Forbidden username check
  function containsForbiddenWord(username) {
    const forbiddenPatterns = [/admin/i, /administrator/i, /superadmin/i, /barangayadmin/i, /root/i, /sysadmin/i];
    return forbiddenPatterns.some(pattern => pattern.test(username));
  }

  // ✅ Live username validation with debounce + AJAX
  let usernameTimeout;
  async function validateUsernameLive() {
    clearTimeout(usernameTimeout);
    usernameTimeout = setTimeout(async () => {
      const value = usernameInput.value.trim();
      const regex = /^[a-zA-Z0-9_]{3,20}$/;

      // Format check
      if (!regex.test(value)) {
        setValidation(usernameInput, false, 'usernameError', 'usernameSuccess');
        document.getElementById('usernameError').textContent = "Username must be 3–20 characters (letters, numbers, underscores)";
        return;
      }

      // Forbidden word check
      if (containsForbiddenWord(value)) {
        usernameInput.classList.add('input-error');
        usernameInput.classList.remove('input-success');
        document.getElementById('usernameError').textContent = "Usernames containing 'admin' or related words are not allowed.";
        document.getElementById('usernameError').style.display = 'block';
        document.getElementById('usernameSuccess').style.display = 'none';
        return;
      }

      // ✅ AJAX username availability check
      try {
        const response = await fetch('register.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'check_username=' + encodeURIComponent(value)
        });
        const data = await response.json();

        if (data.status === 'taken') {
          usernameInput.classList.add('input-error');
          usernameInput.classList.remove('input-success');
          document.getElementById('usernameError').textContent = "Username already taken.";
          document.getElementById('usernameError').style.display = 'block';
          document.getElementById('usernameSuccess').style.display = 'none';
        } else {
          usernameInput.classList.remove('input-error');
          usernameInput.classList.add('input-success');
          document.getElementById('usernameError').style.display = 'none';
          document.getElementById('usernameSuccess').style.display = 'block';
        }
      } catch (err) {
        console.error('Error checking username:', err);
      }
    }, 500); // ✅ delay 0.5s
  }

  // ✅ Event listeners
  fullNameInput.addEventListener('blur', validateFullName);
  usernameInput.addEventListener('input', validateUsernameLive); // live while typing
  passwordInput.addEventListener('blur', validatePassword);
  confirmPasswordInput.addEventListener('blur', validateConfirmPassword);

  // ✅ Final form submit validation
  form.addEventListener('submit', async e => {
    e.preventDefault();

    const nameOk = validateFullName();
    const passOk = validatePassword();
    const confirmOk = validateConfirmPassword();

    // Final username check
    const usernameValid = await new Promise(resolve => {
      clearTimeout(usernameTimeout);
      usernameTimeout = setTimeout(async () => {
        const value = usernameInput.value.trim();
        const regex = /^[a-zA-Z0-9_]{3,20}$/;

        if (!regex.test(value) || containsForbiddenWord(value)) {
          resolve(false);
          return;
        }

        const response = await fetch('register.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'check_username=' + encodeURIComponent(value)
        });
        const data = await response.json();
        resolve(data.status === 'available');
      }, 0);
    });

    if (nameOk && passOk && confirmOk && usernameValid) {
      form.submit(); // ✅ real submit
    } else {
      alert('Please fix the errors before submitting.');
    }
  });
});
</script>


</body>
</html>