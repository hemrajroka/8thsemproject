<?php
require('../db_connection/db_connection.php');
header('Content-Type: application/json'); 
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Handle POST request (add/update/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

   

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        $query = "DELETE FROM colleges WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'College deleted successfully!' : 'Failed to delete college.'
        ]);
        exit();
    }
}

// Handle GET request (fetch all colleges)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM colleges";
    $result = $conn->query($query);
    $colleges = [];

    if ($result && $result->num_rows > 0) {
        $colleges = $result->fetch_all(MYSQLI_ASSOC);
    }

    echo json_encode($colleges);
    exit();
}
?>
