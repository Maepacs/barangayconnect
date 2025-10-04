<?php
session_start();
require_once "../cons/config.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Complaints
$complaints = [];
$cQuery = $conn->query("
    SELECT complaint_id, complaint_title, DATE_FORMAT(date_filed, '%b %d, %Y') AS date_created, u.username
    FROM complaints c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.status = 'New'
    ORDER BY c.date_filed DESC
");
while ($row = $cQuery->fetch_assoc()) {
    $complaints[] = [
        "id" => $row["complaint_id"],
        "title" => $row["complaint_title"],
        "date" => $row["date_created"],
        "sender" => $row["username"],
        "type" => "complaint"
    ];
}

// Document Requests
$documents = [];
$dQuery = $conn->query("
    SELECT request_id, document_type, DATE_FORMAT(date_request, '%b %d, %Y') AS date_created, u.username
    FROM document_request d
    JOIN users u ON d.user_id = u.user_id
    WHERE d.status = 'Pending'
    ORDER BY d.date_request DESC
");
while ($row = $dQuery->fetch_assoc()) {
    $documents[] = [
        "id" => $row["request_id"],
        "title" => $row["document_type"],
        "date" => $row["date_created"],
        "sender" => $row["username"],
        "type" => "document"
    ];  
}

echo json_encode([
    "count" => count($complaints) + count($documents),
    "complaints" => $complaints,
    "documents" => $documents
]);
