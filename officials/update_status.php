<?php
session_start();
include '../cons/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = intval($_POST['request_id']);
    $status = trim($_POST['status']);

    if (!in_array($status, ['Approved', 'Declined', 'Pending', 'Processing'])) {
        exit("Invalid status value.");
    }

    $stmt = $conn->prepare("UPDATE document_request SET status = ? WHERE request_id = ?");
    $stmt->bind_param("si", $status, $request_id);

    echo $stmt->execute()
        ? "Status updated to " . htmlspecialchars($status)
        : "Failed to update status.";
}
?>
