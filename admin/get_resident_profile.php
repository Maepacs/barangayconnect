<?php
require_once "../cons/config.php";
header('Content-Type: application/json');

$user_id = intval($_GET['id'] ?? 0);

if ($user_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid user ID."]);
    exit;
}

// ✅ Step 1: Check if user exists
$user_sql = "SELECT user_id, fullname, username FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if (!$user_result || $user_result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "User not found."]);
    exit;
}

$user = $user_result->fetch_assoc();

// ✅ Step 2: Check if resident profile exists for that user
$res_sql = "
    SELECT resident_id, address, birthdate, contact, occupation, gender
    FROM residents_profile
    WHERE user_id = ?
";
$res_stmt = $conn->prepare($res_sql);
$res_stmt->bind_param("i", $user_id);
$res_stmt->execute();
$res_result = $res_stmt->get_result();

if ($res_result && $res_result->num_rows > 0) {
    $profile = $res_result->fetch_assoc();
    // Merge user + profile data
    echo json_encode([
        "success" => true,
        "resident" => array_merge($user, $profile)
    ]);
} else {
    // ✅ User exists but has no profile
    echo json_encode([
        "success" => false,
        "message" => "No resident profile linked to this user."
    ]);
}

$conn->close();
?>
