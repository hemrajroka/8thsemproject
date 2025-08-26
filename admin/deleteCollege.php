<?php
require('../db_connection/db_connection.php');
header('Content-Type: application/json');
$id = $id = $_GET['id'] ?? null;
// echo($id);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode(["success" => false, "message" => "Missing college ID"]);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM colleges WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "College deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete college"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}

?>
