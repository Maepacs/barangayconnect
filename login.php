<?php
session_start();
require_once "cons/config.php"; // <-- adjust path to your DB connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (!empty($username) && !empty($password)) {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT user_id, fullname, password_hash, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Verify password (assuming hashed with password_hash)
            if (password_verify($password, $row["password_hash"])) {
                // Set session
                $_SESSION["user_id"] = $row["user_id"];
                $_SESSION["fullname"] = $row["fullname"];
                $_SESSION["role"] = $row["role"]; // store role in session

                // Redirect based on role
                if ($row["role"] === "Admin") {
                    header("Location: admin/admin_dashboard.php");
                    exit;
                } elseif ($row["role"] === "Official") {
                    header("Location: officials/officials_dashboard.php");
                    exit;
                } elseif ($row["role"] === "Resident") {
                    header("Location: residents/user_dashboard.php");
                    exit;
                } else {
                    $error = "Unknown user role.";
                }
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Incorrect username or password.";
        }

        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Connect | Login</title>
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
      background: url("assets/images/bg.png") no-repeat center center/cover;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .container {
      position: relative;
      border-radius: 10px;
      overflow: hidden;
      width: 1000%;
      max-width: 400px;
      padding: 30px;
      background: rgba(0, 0, 0, 0.55);
      box-shadow: 0 5px 15px rgba(245, 245, 245, 0.1);
      color: #ffffff;
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

    .input-container, .password-container {
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
      width: 100%;
      padding: 12px 12px 12px 35px;
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
      color: #ccc;
    }

    .form-footer a {
      color: #4a90e2;
      text-decoration: none;
    }

    .form-footer a:hover {
      text-decoration: underline;
    }

    .error-message {
      font-size: 14px;
      margin-top: 5px;
      color: #e74c3c;
      display: none;
    }

    .input-error {
      border-color: #e74c3c;
    }

    .input-success {
      border-color: #2ecc71;
    }

    .server-error {
  margin-bottom: 15px;
  padding: 10px;
  background: rgba(231, 76, 60, 0.1);
  border: 1px solid #e74c3c;
  border-radius: 5px;
  color: #e74c3c;
  font-size: 14px;
  text-align: center;
}

  </style>
</head>
<body>
  <div class="container">
  <h1>Welcome to <br> <span>Barangay Connect</span></h1>
<p class="subtitle">Please login your account</p>

<form id="loginForm" action="login.php" method="POST" novalidate>
  <?php if (!empty($error)): ?>
    <div class="server-error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <div class="form-group">
    <label for="username">Username</label>
    <div class="input-container">
      <i class="fa-solid fa-id-card"></i>
      <input type="text" id="username" name="username" placeholder="Enter your username">
    </div>
    <div class="error-message" id="usernameError">Username is required</div>
  </div>

  <div class="form-group">
    <label for="password">Password</label>
    <div class="password-container">
      <i class="fa-solid fa-lock"></i>
      <input type="password" id="password" name="password" placeholder="Enter your password">
      <button type="button" class="toggle-password" id="togglePassword">
        <i class="fa-solid fa-eye"></i>
      </button>
    </div>
    <div class="error-message" id="passwordError">Password is required</div>
  </div>

  <button type="submit">Login</button>

  <div class="form-footer">
    Don’t have an account? <a href="register.php">Register</a>
  </div>
</form>

  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('loginForm');
      const usernameInput = document.getElementById('username');
      const passwordInput = document.getElementById('password');
      const togglePasswordBtn = document.getElementById('togglePassword');

      // Toggle password visibility
      togglePasswordBtn.addEventListener('click', () => {
        const icon = togglePasswordBtn.querySelector('i');
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
          passwordInput.type = 'password';
          icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
      });

      // Validation
      function validateUsername() {
        const isValid = usernameInput.value.trim() !== '';
        document.getElementById('usernameError').style.display = isValid ? 'none' : 'block';
        usernameInput.classList.toggle('input-error', !isValid);
        usernameInput.classList.toggle('input-success', isValid);
        return isValid;
      }

      function validatePassword() {
        const isValid = passwordInput.value.trim() !== '';
        document.getElementById('passwordError').style.display = isValid ? 'none' : 'block';
        passwordInput.classList.toggle('input-error', !isValid);
        passwordInput.classList.toggle('input-success', isValid);
        return isValid;
      }

      usernameInput.addEventListener('blur', validateUsername);
      passwordInput.addEventListener('blur', validatePassword);

      form.addEventListener('submit', e => {
  const valid = [validateUsername(), validatePassword()].every(Boolean);
  if (!valid) {
    e.preventDefault(); // block only if invalid
    alert('Please fill in all required fields.');
  }
});

    });
  </script>
</body>
</html>
