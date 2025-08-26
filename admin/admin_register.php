<?php
session_start();

require('../db_connection/db_connection.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $name = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');


    if (empty($name) || empty($email) || empty($password)) {
        die("All fields are required.");
    }


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    
    $sql = "INSERT INTO admins (name, email, password) VALUES (?, ?, ?)";


    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    // Execute the query to insert the admin
    if ($stmt->execute()) {
        
        header("Location: admin_login.html");
        exit();
    } else {
        // Handle duplicate email error
        if ($conn->errno === 1062) { // MySQL error code for duplicate entry
            die("An account with this email already exists.");
        }
        die("Error: " . $stmt->error);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    die("Invalid request method.");

}
?>