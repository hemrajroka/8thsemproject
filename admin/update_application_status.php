<?php
require('../db_connection/db_connection.php'); // adjust path as needed
header('Content-Type: application/json');

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = intval($_POST['application_id'] ?? 0);
    $new_status = trim($_POST['status'] ?? '');

    // Validate status
    $allowed_statuses = ['pending', 'approved', 'rejected'];
    if (!in_array($new_status, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
        exit();
    }

    // Update query
    $query = "UPDATE applications SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $new_status, $application_id);
    $result = $stmt->execute();

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Application status updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update application status.']);
    }

    $stmt->close();
    $conn->close();
    exit();
}

// Invalid request method
echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
exit();
?>
