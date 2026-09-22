<?php
session_start();

require('../db_connection/db_connection.php'); 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    
    $username = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    
    if (empty($username) || empty($password)) {
        echo "Please fill out all fields.";
        exit();
    }

    
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE email = ?");
    if (!$stmt) {
        echo "SQL Error (Admin): " . $conn->error;
        exit();
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $adminResult = $stmt->get_result();

    
    if ($adminResult && $adminResult->num_rows > 0) {
        $user = $adminResult->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['adminID'] = $user['id']; 
            header("Location: /8thsem_project/admin/admin_dashboard.html");
            exit();
        } else {
            echo "Invalid username or password.";
            exit();
        }
    }

    echo "No account found with this email.";
    exit();
} else {
    echo "Invalid request method.";
    exit();
}
?>