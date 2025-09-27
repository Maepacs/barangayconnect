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


/* Background image with blur */
.container::before {
  content: "";
  position: absolute;
  inset: 0;
  background-size: cover;
  filter: blur(5px); controls blur level
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
    <form id="registrationForm" novalidate>
    <div class="form-group">
  <label for="fullName">Name</label>
  <div class="input-container">
    <i class="fa-solid fa-user"></i>
    <input type="text" id="fullName" name="fullName" placeholder="Enter your full name">
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
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('registrationForm');
      const fullNameInput = document.getElementById('fullName');
      const usernameInput = document.getElementById('username');
      const passwordInput = document.getElementById('password');
      const confirmPasswordInput = document.getElementById('confirmPassword');
      const togglePasswordBtn = document.getElementById('togglePassword');
      const toggleConfirmPasswordBtn = document.getElementById('toggleConfirmPassword');

      // Toggle password visibility
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

      // Validation functions
      function validateFullName() {
        return setValidation(fullNameInput, fullNameInput.value.trim().length >= 2, 'fullNameError');
      }

      function validateUsername() {
        const regex = /^[a-zA-Z0-9_]{3,20}$/;
        const isValid = regex.test(usernameInput.value.trim());
        return setValidation(usernameInput, isValid, 'usernameError', 'usernameSuccess');
      }

      function validatePassword() {
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;
        return setValidation(passwordInput, regex.test(passwordInput.value), 'passwordError');
      }

      function validateConfirmPassword() {
        const isValid = confirmPasswordInput.value === passwordInput.value && confirmPasswordInput.value !== '';
        return setValidation(confirmPasswordInput, isValid, 'confirmPasswordError');
      }

      // Helper for showing errors/success
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

      // Events
      fullNameInput.addEventListener('blur', validateFullName);
      usernameInput.addEventListener('blur', validateUsername);
      passwordInput.addEventListener('blur', validatePassword);
      confirmPasswordInput.addEventListener('blur', validateConfirmPassword);

      // Form submit
      form.addEventListener('submit', e => {
        e.preventDefault();
        const valid = [
          validateFullName(),
          validateUsername(),
          validatePassword(),
          validateConfirmPassword()
        ].every(Boolean);

        if (valid) {
          alert('Registration successful!');
          form.reset();
          form.querySelectorAll('input').forEach(i => i.classList.remove('input-success'));
          document.querySelectorAll('.success-message').forEach(m => m.style.display = 'none');
        } else {
          alert('Please fix the errors before submitting.');
        }
      });
    });
  </script>
</body>
</html>
