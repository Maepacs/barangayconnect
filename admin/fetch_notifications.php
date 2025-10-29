<?php
session_start();
require_once "../cons/config.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$adminId = $_SESSION["user_id"];

// Complaints (with read/unread status)
$complaints = [];
$cQuery = $conn->query("
    SELECT c.complaint_id, c.complaint_title, DATE_FORMAT(c.date_filed, '%b %d, %Y') AS date_created, u.username,
           CASE WHEN anr.ref_id IS NULL THEN 'Unread' ELSE 'Read' END AS status
    FROM complaints c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN admin_notification_reads anr
      ON anr.user_id = '" . $conn->real_escape_string($adminId) . "' AND anr.type='complaint' AND anr.ref_id = c.complaint_id
    WHERE c.status = 'New'
    ORDER BY c.date_filed DESC
");
while ($row = $cQuery->fetch_assoc()) {
    $complaints[] = [
        "id" => $row["complaint_id"],
        "title" => $row["complaint_title"],
        "date" => $row["date_created"],
        "sender" => $row["username"],
        "type" => "complaint",
        "status" => $row["status"]
    ];
}

// Document Requests (with read/unread status)
$documents = [];
$dQuery = $conn->query("
    SELECT d.request_id, d.document_type, DATE_FORMAT(d.date_requested, '%b %d, %Y') AS date_created, u.username,
           CASE WHEN anr.ref_id IS NULL THEN 'Unread' ELSE 'Read' END AS status
    FROM document_request d
    JOIN users u ON d.user_id = u.user_id
    LEFT JOIN admin_notification_reads anr
      ON anr.user_id = '" . $conn->real_escape_string($adminId) . "' AND anr.type='document' AND anr.ref_id = d.request_id
    WHERE d.status = 'Pending'
    ORDER BY d.date_requested DESC
");
while ($row = $dQuery->fetch_assoc()) {
    $documents[] = [
        "id" => $row["request_id"],
        "title" => $row["document_type"],
        "date" => $row["date_created"],
        "sender" => $row["username"],
        "type" => "document",
        "status" => $row["status"]
    ];  
}

// unread count only
$unreadCount = 0;
foreach ($complaints as $c) { if ($c['status'] === 'Unread') $unreadCount++; }
foreach ($documents as $d) { if ($d['status'] === 'Unread') $unreadCount++; }

echo json_encode([
    "count" => $unreadCount,
    "complaints" => $complaints,
    "documents" => $documents
]);
