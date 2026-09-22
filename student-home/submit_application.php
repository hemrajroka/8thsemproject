<?php

session_start();

require('../db_connection/db_connection.php');

header('Content-Type: application/json');


/*
|--------------------------------------------------------------------------
| Check Student Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['student_id'])) {

    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login again.'
    ]);

    exit();
}

$studentID = (int) $_SESSION['student_id'];


/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$collegeId = $_POST['college_id'] ?? '';
$entranceRank = $_POST['entrance_rank'] ?? '';
$course = trim($_POST['applied_course'] ?? '');
$backgroundFaculty = trim($_POST['plus_two_faculty'] ?? '');


/*
|--------------------------------------------------------------------------
| Validate Required Fields
|--------------------------------------------------------------------------
*/

if (
    empty($collegeId) ||
    empty($entranceRank) ||
    empty($course) ||
    empty($backgroundFaculty)
) {

    echo json_encode([
        'success' => false,
        'message' => 'All fields are required.'
    ]);

    exit();
}

$collegeId = (int) $collegeId;
$entranceRank = (int) $entranceRank;


/*
|--------------------------------------------------------------------------
| Check Existing Application
|--------------------------------------------------------------------------
*/

$checkSql = "
    SELECT status
    FROM applications
    WHERE student_id = ?
    AND college_id = ?
    AND status != 'Rejected'
";

$checkStmt = $conn->prepare($checkSql);

if (!$checkStmt) {

    echo json_encode([
        'success' => false,
        'message' => 'Database prepare error: ' . $conn->error
    ]);

    exit();
}

$checkStmt->bind_param(
    "ii",
    $studentID,
    $collegeId
);

$checkStmt->execute();

$checkStmt->store_result();


if ($checkStmt->num_rows > 0) {

    echo json_encode([
        'success' => false,
        'message' =>
            'You already have a pending or approved application for this college. ' .
            'You cannot apply again unless your previous application is rejected.'
    ]);

    $checkStmt->close();
    $conn->close();

    exit();
}

$checkStmt->close();


/*
|--------------------------------------------------------------------------
| Upload Marksheet
|--------------------------------------------------------------------------
*/

$uploadDir = "../uploads/";

if (!is_dir($uploadDir)) {

    if (!mkdir($uploadDir, 0755, true)) {

        echo json_encode([
            'success' => false,
            'message' => 'Unable to create upload directory.'
        ]);

        exit();
    }
}


/*
|--------------------------------------------------------------------------
| Check Marksheet
|--------------------------------------------------------------------------
*/

if (
    !isset($_FILES['marksheet']) ||
    $_FILES['marksheet']['error'] !== UPLOAD_ERR_OK
) {

    echo json_encode([
        'success' => false,
        'message' => 'Marksheet file is required.'
    ]);

    exit();
}


$originalFilename = basename($_FILES['marksheet']['name']);

$fileExtension = strtolower(
    pathinfo($originalFilename, PATHINFO_EXTENSION)
);


$allowedTypes = [
    'pdf',
    'jpg',
    'jpeg',
    'png'
];


if (!in_array($fileExtension, $allowedTypes, true)) {

    echo json_encode([
        'success' => false,
        'message' =>
            'Invalid file type. Allowed: PDF, JPG, JPEG, PNG.'
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| Generate Unique File Name
|--------------------------------------------------------------------------
*/

$newFilename =
    $studentID . '_' .
    time() . '_' .
    preg_replace(
        '/[^a-zA-Z0-9._-]/',
        '_',
        $originalFilename
    );


$targetPath = $uploadDir . $newFilename;


/*
|--------------------------------------------------------------------------
| Move Uploaded File
|--------------------------------------------------------------------------
*/

if (
    !move_uploaded_file(
        $_FILES['marksheet']['tmp_name'],
        $targetPath
    )
) {

    echo json_encode([
        'success' => false,
        'message' => 'Failed to upload marksheet.'
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| Save Application
|--------------------------------------------------------------------------
*/

$sql = "
    INSERT INTO applications
    (
        student_id,
        college_id,
        entrance_rank,
        course,
        background_faculty,
        marksheet_path
    )
    VALUES (?, ?, ?, ?, ?, ?)
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    /*
    | Delete uploaded file if database preparation fails
    */

    if (file_exists($targetPath)) {
        unlink($targetPath);
    }

    echo json_encode([
        'success' => false,
        'message' =>
            'Database prepare error: ' . $conn->error
    ]);

    exit();
}


$stmt->bind_param(
    "iiisss",
    $studentID,
    $collegeId,
    $entranceRank,
    $course,
    $backgroundFaculty,
    $targetPath
);


/*
|--------------------------------------------------------------------------
| Execute
|--------------------------------------------------------------------------
*/

if ($stmt->execute()) {

    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully.'
    ]);

} else {

    /*
    | Delete uploaded file if database insert fails
    */

    if (file_exists($targetPath)) {
        unlink($targetPath);
    }

    echo json_encode([
        'success' => false,
        'message' =>
            'Database execute error: ' . $stmt->error
    ]);
}


$stmt->close();
$conn->close();

?>