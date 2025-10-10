<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = trim($_POST["password"]);

    if (!empty($password)) {
        // Generate the hashed password
        $hashed = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $error = "Please enter a password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Password Hash Generator</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
        padding: 40px;
        display: flex;
        justify-content: center;
    }
    .container {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        width: 400px;
    }
    input[type="password"], input[type="text"] {
        width: 100%;
        padding: 10px;
        margin-top: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    button {
        margin-top: 15px;
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
    }
    button:hover {
        background-color: #0056b3;
    }
    .hash-box {
        margin-top: 15px;
        background: #f0f0f0;
        padding: 10px;
        border-radius: 5px;
        word-break: break-all;
        font-family: monospace;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Password Hash Generator</h2>
    <form method="POST">
        <label>Enter Password:</label>
        <input type="password" name="password" placeholder="Type password..." required>
        <button type="submit">Generate Hash</button>
    </form>

    <?php if (isset($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (isset($hashed)): ?>
        <div class="hash-box">
            <strong>Generated Hash:</strong><br>
            <?= htmlspecialchars($hashed) ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
