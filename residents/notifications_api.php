<?php
session_start();
require_once "../cons/config.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Resident") {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION["user_id"];

// Ensure notifications table exists (idempotent)
$conn->query("CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id VARCHAR(20) NOT NULL,
  type ENUM('complaint','document','system') NOT NULL,
  ref_id VARCHAR(64) DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT DEFAULT NULL,
  status ENUM('Unread','Read') NOT NULL DEFAULT 'Unread',
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX(user_id), INDEX(status), INDEX(type), INDEX(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

// Helpers
function respond($data) { echo json_encode($data); exit; }

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if ($action === 'list') {
    $page = max(1, intval($_GET['page'] ?? 1));
    $pageSize = min(50, max(5, intval($_GET['page_size'] ?? 10)));
    $offset = ($page - 1) * $pageSize;

    // Fetch notifications
    $stmt = $conn->prepare("SELECT id, type, ref_id, title, message, status, DATE_FORMAT(created_at, '%b %d, %Y %h:%i %p') AS date
                            FROM notifications
                            WHERE user_id = ? AND is_deleted = 0
                            ORDER BY created_at DESC
                            LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $user_id, $pageSize, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    $items = [];
    while ($row = $res->fetch_assoc()) { $items[] = $row; }
    $stmt->close();

    // Counts
    $total = 0; $unread = 0;
    $c1 = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_deleted=0");
    $c1->bind_param("s", $user_id); $c1->execute(); $c1->bind_result($total); $c1->fetch(); $c1->close();
    $c2 = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_deleted=0 AND status='Unread'");
    $c2->bind_param("s", $user_id); $c2->execute(); $c2->bind_result($unread); $c2->fetch(); $c2->close();

    respond(["success"=>true, "items"=>$items, "page"=>$page, "page_size"=>$pageSize, "total"=>$total, "unread"=>$unread]);
}

if (in_array($action, ['mark_read','mark_unread','delete','dismiss'])) {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) respond(["success"=>false, "error"=>"Invalid id"]);

    if ($action === 'delete') {
        $stmt = $conn->prepare("UPDATE notifications SET is_deleted=1 WHERE id=? AND user_id=?");
        $stmt->bind_param("is", $id, $user_id);
        $ok = $stmt->execute();
        $stmt->close();
        respond(["success"=>$ok]);
    }

    if ($action === 'dismiss' || $action === 'mark_read') {
        $newStatus = 'Read';
    } else { // mark_unread
        $newStatus = 'Unread';
    }
    $stmt = $conn->prepare("UPDATE notifications SET status=? WHERE id=? AND user_id=?");
    $stmt->bind_param("sis", $newStatus, $id, $user_id);
    $ok = $stmt->execute();
    $stmt->close();
    respond(["success"=>$ok, "status"=>$newStatus]);
}

if ($action === 'mark_all_read') {
    $stmt = $conn->prepare("UPDATE notifications SET status='Read' WHERE user_id=? AND is_deleted=0");
    $stmt->bind_param("s", $user_id);
    $ok = $stmt->execute();
    $stmt->close();
    respond(["success"=>$ok]);
}

respond(["success"=>false, "error"=>"Unknown action"]);
?>

