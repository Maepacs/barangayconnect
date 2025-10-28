<?php
session_start();
require_once "../cons/config.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Resident") {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION["user_id"];

// Complaints for this resident
$complaints = [];
$cQuery = $conn->prepare("
    SELECT complaint_id, complaint_title, DATE_FORMAT(date_filed, '%b %d, %Y') AS date_created, status
    FROM complaints
    WHERE user_id = ?
    ORDER BY date_filed DESC
    LIMIT 20
");
$cQuery->bind_param("s", $user_id);
$cQuery->execute();
$cRes = $cQuery->get_result();
while ($row = $cRes->fetch_assoc()) {
    $complaints[] = [
        "id" => $row["complaint_id"],
        "title" => $row["complaint_title"],
        "date" => $row["date_created"],
        "status" => $row["status"],
        "type" => "complaint"
    ];
}
$cQuery->close();

// Document requests for this resident
$documents = [];
$dQuery = $conn->prepare("
    SELECT request_id, document_type, DATE_FORMAT(date_requested, '%b %d, %Y') AS date_created, status
    FROM document_request
    WHERE user_id = ?
    ORDER BY date_requested DESC
    LIMIT 20
");
$dQuery->bind_param("s", $user_id);
$dQuery->execute();
$dRes = $dQuery->get_result();
while ($row = $dRes->fetch_assoc()) {
    $documents[] = [
        "id" => $row["request_id"],
        "title" => $row["document_type"],
        "date" => $row["date_created"],
        "status" => $row["status"],
        "type" => "document"
    ];
}
$dQuery->close();

echo json_encode([
    "count" => count($complaints) + count($documents),
    "complaints" => $complaints,
    "documents" => $documents
]);
?>

