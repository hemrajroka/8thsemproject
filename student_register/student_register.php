<?php

require('../db_connection/db_connection.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');


if ($conn->connect_error) {

    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.'
    ]);

    exit();
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = $conn->real_escape_string($_POST['fullname'] ?? '');
    $gender   = $conn->real_escape_string($_POST['gender'] ?? '');
    $email    = $conn->real_escape_string($_POST['email'] ?? '');
    $passwordRaw = $_POST['password'] ?? '';


    if (empty($fullname) || empty($gender) || empty($email) || empty($passwordRaw)) {

        echo json_encode([
            'success' => false,
            'message' => 'Please fill all required fields.'
        ]);

        exit();
    }


    /*
     * Check if email already exists
     */

    $checkSql = "SELECT id FROM students WHERE email = '$email'";

    $checkResult = $conn->query($checkSql);


    if ($checkResult && $checkResult->num_rows > 0) {

        echo json_encode([
            'success' => false,
            'message' => 'This email is already registered.'
        ]);

        exit();
    }


    /*
     * Hash password before storing
     */

    $password = password_hash($passwordRaw, PASSWORD_DEFAULT);


    /*
     * Insert registration information
     */

    $sql = "INSERT INTO students
            (`fullname`, `gender`, `email`, `password`, `created_at`)
            VALUES
            ('$fullname', '$gender', '$email', '$password', NOW())";


    if ($conn->query($sql) === TRUE) {

        $student_id = $conn->insert_id;


        /*
         * Store student information in session
         */

        $_SESSION['student_id'] = $student_id;
        $_SESSION['fullname'] = $fullname;
        $_SESSION['email'] = $email;


        echo json_encode([
            'success' => true,
            'message' => 'Registration successful.'
        ]);

    } else {

        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $conn->error
        ]);

    }

} else {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);

}


$conn->close();

?>