<?php
session_start();
require_once "../cons/config.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

if (isset($_POST["id"], $_POST["type"])) {
    $id = intval($_POST["id"]);
    $type = $_POST["type"];

    if ($type === "complaint") {
        $conn->query("UPDATE complaints SET status='Read' WHERE complaint_id=$id");
        echo json_encode(["success" => true, "redirect" => "complaints.php"]);
        exit;
    } elseif ($type === "document") {
        $conn->query("UPDATE document_request SET status='Reviewed' WHERE request_id=$id");
        echo json_encode(["success" => true, "redirect" => "document_request.php"]);
        exit;
    }
}

echo json_encode(["success" => false, "message" => "Invalid request"]);
