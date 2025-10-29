<?php
session_start();
require_once "../cons/config.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$adminId = $_SESSION["user_id"]; // assumes admins are in users table

// Ensure read-tracking table exists (idempotent)
$conn->query("CREATE TABLE IF NOT EXISTS admin_notification_reads (
  user_id VARCHAR(20) NOT NULL,
  type ENUM('complaint','document') NOT NULL,
  ref_id VARCHAR(64) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, type, ref_id),
  INDEX(user_id), INDEX(type), INDEX(ref_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

function respond($data) { echo json_encode($data); exit; }

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'mark_read') {
    $type = $_POST['type'] ?? '';
    $refId = $_POST['id'] ?? '';
    if (!in_array($type, ['complaint','document'], true) || $refId === '') {
        respond(["success" => false, "error" => "Invalid params"]);
    }
    $stmt = $conn->prepare("INSERT IGNORE INTO admin_notification_reads (user_id, type, ref_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $adminId, $type, $refId);
    $ok = $stmt->execute();
    $stmt->close();
    respond(["success" => $ok]);
}

if ($action === 'mark_unread') {
    $type = $_POST['type'] ?? '';
    $refId = $_POST['id'] ?? '';
    if (!in_array($type, ['complaint','document'], true) || $refId === '') {
        respond(["success" => false, "error" => "Invalid params"]);
    }
    $stmt = $conn->prepare("DELETE FROM admin_notification_reads WHERE user_id=? AND type=? AND ref_id=?");
    $stmt->bind_param("sss", $adminId, $type, $refId);
    $ok = $stmt->execute();
    $stmt->close();
    respond(["success" => $ok]);
}

if ($action === 'mark_all_read') {
    // Expect JSON array of items: [{id, type}]
    $raw = $_POST['items'] ?? '[]';
    $items = json_decode($raw, true);
    if (!is_array($items)) { $items = []; }
    if (empty($items)) { respond(["success" => true]); }

    $stmt = $conn->prepare("INSERT IGNORE INTO admin_notification_reads (user_id, type, ref_id) VALUES (?, ?, ?)");
    foreach ($items as $it) {
        $type = $it['type'] ?? '';
        $refId = $it['id'] ?? '';
        if (!in_array($type, ['complaint','document'], true) || $refId === '') continue;
        $stmt->bind_param("sss", $adminId, $type, $refId);
        $stmt->execute();
    }
    $stmt->close();
    respond(["success" => true]);
}

respond(["success" => false, "error" => "Unknown action"]);
?>


