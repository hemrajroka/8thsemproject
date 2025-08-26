<?php
require('../db_connection/db_connection.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');


if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = $conn->real_escape_string($_POST['fullname']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $email = $conn->real_escape_string($_POST['email']);
    $city = $conn->real_escape_string($_POST['city']);
    $passwordRaw = $conn->real_escape_string($_POST['password']);
    $qualification = $conn->real_escape_string($_POST['qualification']);
    $university = $conn->real_escape_string($_POST['university']);

  
    $password = password_hash($passwordRaw, PASSWORD_BCRYPT);

    $courseRaw = $_POST['course'] ?? '[]';
    $courses = json_decode($courseRaw, true);

    if (!is_array($courses) || count($courses) === 0) {
        echo json_encode(['success' => false, 'message' => 'Please select at least one course.']);
        exit();
    }

    // Convert back to JSON string for storing in DB
    $course_json = $conn->real_escape_string(json_encode($courses));

   
    $sql = "INSERT INTO students (fullname, gender, email, city, password, qualification, university, course, created_at)
            VALUES ('$fullname', '$gender', '$email', '$city', '$password', '$qualification', '$university', '$course_json', NOW())";

    if ($conn->query($sql) === TRUE) {
        $student_id = $conn->insert_id;

        // Store in session (optional)
        $_SESSION['student_id'] = $student_id;
        $_SESSION['university'] = $university;
        $_SESSION['course'] = $courses;

        echo json_encode(['success' => true, 'message' => 'Registration successful.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
