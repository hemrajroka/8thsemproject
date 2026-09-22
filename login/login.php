<?php

session_start();

require('../db_connection/db_connection.php');


/*
|--------------------------------------------------------------------------
| Check Database Connection
|--------------------------------------------------------------------------
*/

if ($conn->connect_error) {

    die("Connection failed: " . $conn->connect_error);

}


/*
|--------------------------------------------------------------------------
| Handle Login
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');

    $password = trim($_POST['password'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validate Fields
    |--------------------------------------------------------------------------
    */

    if (empty($email) || empty($password)) {

        echo "Please fill out all fields.";

        exit();

    }


    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare(
        "SELECT id, fullname, email, password
         FROM students
         WHERE email = ?"
    );


    if (!$stmt) {

        echo "Database error: " . $conn->error;

        exit();

    }


    $stmt->bind_param("s", $email);

    $stmt->execute();

    $result = $stmt->get_result();


    /*
    |--------------------------------------------------------------------------
    | Check Student
    |--------------------------------------------------------------------------
    */

    if ($result && $result->num_rows > 0) {

        $user = $result->fetch_assoc();


        /*
        |--------------------------------------------------------------------------
        | Verify Password
        |--------------------------------------------------------------------------
        */

        if (password_verify($password, $user['password'])) {


            /*
            |--------------------------------------------------------------------------
            | Create Consistent Session
            |--------------------------------------------------------------------------
            */

            $_SESSION['student_id'] = (int) $user['id'];

            $_SESSION['fullname'] = $user['fullname'];

            $_SESSION['email'] = $user['email'];


            /*
            |--------------------------------------------------------------------------
            | Remove Old Session Name
            |--------------------------------------------------------------------------
            */

            unset($_SESSION['studentID']);


            /*
            |--------------------------------------------------------------------------
            | Redirect
            |--------------------------------------------------------------------------
            */

            header(
                "Location: /8thsem_project/student-home/view_all_colleges.php"
            );

            exit();


        } else {

            echo "Incorrect password.";

        }


    } else {

        echo "Email not found.";

    }


    $stmt->close();

}


$conn->close();

?>