<?php
session_start();
require('../db_connection/db_connection.php');

if (!isset($_SESSION['studentID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$studentID = $_SESSION['studentID'];

$collegeId = $_POST['college_id'] ?? '';
$entranceRank = $_POST['entrance_rank'] ?? '';
$course = $_POST['applied_course'] ?? '';
$backgroundFaculty = $_POST['plus_two_faculty'] ?? '';


if (empty($collegeId) || empty($entranceRank) || empty($course) || empty($backgroundFaculty)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}


$checkSql = "SELECT status FROM applications WHERE student_id = ? AND college_id = ? AND status != 'Rejected'";
$checkStmt = $conn->prepare($checkSql);
if (!$checkStmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
    exit();
}
$checkStmt->bind_param("ii", $studentID, $collegeId);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You already have a pending or approved application for this college. You cannot apply again unless your previous application is rejected.']);
    $checkStmt->close();
    $conn->close();
    exit();
}
$checkStmt->close();

// Handle file upload for marksheet
$uploadDir = "../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (isset($_FILES['marksheet']) && $_FILES['marksheet']['error'] === 0) {
    $filename = basename($_FILES['marksheet']['name']);
    $targetPath = $uploadDir . time() . "_" . $filename;

    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
    $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: pdf, jpg, jpeg, png.']);
        exit();
    }

    if (!move_uploaded_file($_FILES['marksheet']['tmp_name'], $targetPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload marksheet.']);
        exit();
    }
    $filePath = $targetPath;
} else {
    echo json_encode(['success' => false, 'message' => 'Marksheet file is required.']);
    exit();
}

$sql = "INSERT INTO applications (student_id, college_id, entrance_rank, course, background_faculty, marksheet_path) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
    exit();
}

// Bind parameters and execute
$stmt->bind_param("iiisss", $studentID, $collegeId, $entranceRank, $course, $backgroundFaculty, $filePath);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Application submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database execute error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
