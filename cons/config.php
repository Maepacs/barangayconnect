<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "barangay_connect";

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
