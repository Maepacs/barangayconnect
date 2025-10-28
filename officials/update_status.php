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
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        // Fetch request info to notify the resident
        $q = $conn->prepare("SELECT user_id, document_type, tracking_number FROM document_request WHERE request_id = ?");
        $q->bind_param("i", $request_id);
        $q->execute();
        $q->bind_result($userId, $docType, $trackNo);
        if ($q->fetch()) {
            $q->close();
            // Ensure notifications table exists (if resident pages haven't)
            $conn->query("CREATE TABLE IF NOT EXISTS notifications (
              id INT AUTO_INCREMENT PRIMARY KEY,
              user_id VARCHAR(20) NOT NULL,
              type ENUM('complaint','document','system') NOT NULL,
              ref_id VARCHAR(64) DEFAULT NULL,
              title VARCHAR(255) NOT NULL,
              message TEXT DEFAULT NULL,
              status ENUM('Unread','Read') NOT NULL DEFAULT 'Unread',
              is_deleted TINYINT(1) NOT NULL DEFAULT 0,
              created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $title = 'Document Request ' . $status;
            $msg = 'Your request for ' . $docType . ' is now ' . $status . '. Tracking: ' . $trackNo;
            $ins = $conn->prepare("INSERT INTO notifications (user_id, type, ref_id, title, message) VALUES (?, 'document', ?, ?, ?)");
            $ref = (string)$request_id;
            $ins->bind_param("ssss", $userId, $ref, $title, $msg);
            $ins->execute();
            $ins->close();
        } else {
            $q->close();
        }
        echo "Status updated to " . htmlspecialchars($status);
    } else {
        echo "Failed to update status.";
    }
}
?>
