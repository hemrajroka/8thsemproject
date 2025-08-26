<?php
require('../db_connection/db_connection.php');  // Your DB connection file

header('Content-Type: application/json'); // Return JSON

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Connection failed: ' . $conn->connect_error
    ]);
    exit();
}

$sql = "SELECT id, name, course, university, location, phone_number, scholarships, duration FROM colleges";
$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Query error: ' . $conn->error
    ]);
    $conn->close();
    exit();
}

$colleges = [];

while ($row = $result->fetch_assoc()) {
    $colleges[] = $row;
}

echo json_encode($colleges);

$conn->close();
