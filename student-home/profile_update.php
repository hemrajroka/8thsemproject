<?php

session_start();

require('../db_connection/db_connection.php');


/* =====================================================
   DATABASE CONNECTION
===================================================== */

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


/* =====================================================
   SESSION
===================================================== */

if (isset($_SESSION['student_id'])) {

    $studentID = (int) $_SESSION['student_id'];

} elseif (isset($_SESSION['studentID'])) {

    $studentID = (int) $_SESSION['studentID'];

    $_SESSION['student_id'] = $studentID;

} else {

    header("Location: /8thsem_project/login/login.html");
    exit();

}


$error = '';
$success = '';


/* =====================================================
   UPDATE PROFILE
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['fullname'] ?? '');

    $gender = trim($_POST['gender'] ?? '');

    $email = trim($_POST['email'] ?? '');

    $old_password = $_POST['old_password'] ?? '';

    $new_password = $_POST['new_password'] ?? '';


    /* =================================================
       BASIC VALIDATION
    ================================================= */

    if (empty($fullname) || empty($gender) || empty($email)) {

        $error = "Please fill in all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    }


    /* =================================================
       CHECK EMAIL
    ================================================= */

    if (!$error) {

        $stmt = $conn->prepare(
            "SELECT id
             FROM students
             WHERE email = ?
             AND id != ?"
        );

        $stmt->bind_param(
            "si",
            $email,
            $studentID
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $error = "This email address is already registered.";

        }

        $stmt->close();
    }


    /* =================================================
       PASSWORD CHANGE
    ================================================= */

    if (!$error && ($old_password !== '' || $new_password !== '')) {

        if ($old_password === '' || $new_password === '') {

            $error =
                "To change your password, both old and new password fields must be filled.";

        } elseif (strlen($new_password) < 6) {

            $error =
                "New password must be at least 6 characters long.";

        } else {

            $stmt = $conn->prepare(
                "SELECT password
                 FROM students
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "i",
                $studentID
            );

            $stmt->execute();

            $result = $stmt->get_result();

            $row = $result->fetch_assoc();

            $stmt->close();


            if (
                !$row ||
                !password_verify(
                    $old_password,
                    $row['password']
                )
            ) {

                $error = "Old password is incorrect.";

            }

        }

    }


    /* =================================================
       UPDATE DATABASE
    ================================================= */

    if (!$error) {

        if (
            $old_password !== '' &&
            $new_password !== ''
        ) {

            $hashed_password =
                password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );


            $sql = "
                UPDATE students
                SET
                    fullname = ?,
                    gender = ?,
                    email = ?,
                    password = ?
                WHERE id = ?
            ";


            $stmt = $conn->prepare($sql);


            $stmt->bind_param(
                "ssssi",
                $fullname,
                $gender,
                $email,
                $hashed_password,
                $studentID
            );

        } else {

            $sql = "
                UPDATE students
                SET
                    fullname = ?,
                    gender = ?,
                    email = ?
                WHERE id = ?
            ";


            $stmt = $conn->prepare($sql);


            $stmt->bind_param(
                "sssi",
                $fullname,
                $gender,
                $email,
                $studentID
            );

        }


        if ($stmt->execute()) {

            /*
            |--------------------------------------------------------------------------
            | Update Session Information
            |--------------------------------------------------------------------------
            */

            $_SESSION['fullname'] = $fullname;

            $_SESSION['email'] = $email;


            $success =
                "Profile updated successfully.";

        } else {

            $error =
                "Failed to update profile.";

        }


        $stmt->close();
    }

}


/* =====================================================
   FETCH CURRENT STUDENT DATA
===================================================== */

$stmt = $conn->prepare(
    "SELECT
        fullname,
        gender,
        email
     FROM students
     WHERE id = ?"
);

$stmt->bind_param(
    "i",
    $studentID
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 1) {

    $student = $result->fetch_assoc();

} else {

    die("Student profile not found.");

}

$stmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Edit Profile</title>


    <style>

        /* =====================================================
           BODY
        ===================================================== */

        body {

            margin: 0;

            font-family:
                'Segoe UI',
                Tahoma,
                Geneva,
                Verdana,
                sans-serif;

            background-color: #f4f6f8;

            color: #333;

            display: flex;

        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {

            width: 220px;

            background-color: #1c3a66;

            height: 100vh;

            padding-top: 1rem;

            position: fixed;

            left: 0;

            top: 0;

            display: flex;

            flex-direction: column;

            box-shadow:
                2px 0 5px
                rgba(0, 0, 0, 0.1);

            z-index: 1000;

        }


        .sidebar a {

            text-decoration: none;

            color: #f7b733;

            font-weight: 500;

            padding: 0.75rem 1rem;

            display: block;

            transition:
                background 0.2s ease;

        }


        .sidebar a:hover {

            background-color: #274c87;

        }


        /* =====================================================
           MAIN CONTENT
        ===================================================== */

        .main-content {

            margin-left: 220px;

            padding: 2rem;

            width:
                calc(100% - 220px);

            box-sizing: border-box;

        }


        /* =====================================================
           PAGE HEADER
        ===================================================== */

        .page-header {

            margin-bottom: 2rem;

        }


        .page-header h1 {

            margin: 0;

            color: #1c3a66;

            font-size: 28px;

        }


        .page-header p {

            margin-top: 8px;

            color: #666;

            font-size: 15px;

        }


        /* =====================================================
           PROFILE FORM
        ===================================================== */

        .profile-container {

            background: #fff;

            border:
                1px solid #ccc;

            border-radius: 10px;

            padding: 1.5rem;

            max-width: 600px;

            box-shadow:
                0 2px 5px
                rgba(0,0,0,0.1);

        }


        .profile-container label {

            display: block;

            margin-top: 15px;

            margin-bottom: 6px;

            font-weight: bold;

            color: #274c87;

        }


        .profile-container input,
        .profile-container select {

            width: 100%;

            padding: 10px;

            border:
                1px solid #ccc;

            border-radius: 6px;

            box-sizing: border-box;

            font-size: 15px;

            outline: none;

        }


        .profile-container input:focus,
        .profile-container select:focus {

            border-color:
                #1c3a66;

        }


        /* =====================================================
           PASSWORD SECTION
        ===================================================== */

        .password-section {

            margin-top: 25px;

            padding-top: 20px;

            border-top:
                1px solid #ddd;

        }


        .password-section h3 {

            color: #1c3a66;

            margin-top: 0;

        }


        .password-note {

            color: #777;

            font-size: 14px;

            margin-bottom: 15px;

        }


        /* =====================================================
           BUTTON
        ===================================================== */

        .update-btn {

            width: 100%;

            padding: 12px;

            margin-top: 25px;

            background-color:
                #1c3a66;

            color: white;

            font-size: 16px;

            font-weight: bold;

            border: none;

            border-radius: 6px;

            cursor: pointer;

        }


        .update-btn:hover {

            background-color:
                #274c87;

        }


        /* =====================================================
           MESSAGES
        ===================================================== */

        .error {

            background-color:
                #f8d7da;

            color:
                #721c24;

            border:
                1px solid #f5c6cb;

            padding: 12px;

            border-radius: 6px;

            margin-bottom: 20px;

            font-weight: 500;

        }


        .success {

            background-color:
                #d4edda;

            color:
                #155724;

            border:
                1px solid #c3e6cb;

            padding: 12px;

            border-radius: 6px;

            margin-bottom: 20px;

            font-weight: 500;

        }


        /* =====================================================
           CHATBOT
        ===================================================== */

        #chat-toggle {

            position: fixed;

            right: 25px;

            bottom: 25px;

            width: 62px;

            height: 62px;

            border-radius: 50%;

            border: none;

            background-color:
                #1c3a66;

            color: white;

            font-size: 28px;

            cursor: pointer;

            box-shadow:
                0 4px 12px
                rgba(0,0,0,0.25);

            z-index: 9999;

            transition:
                transform 0.2s ease;

        }


        #chat-toggle:hover {

            transform:
                scale(1.05);

            background-color:
                #274c87;

        }


        #chat-container {

            position: fixed;

            right: 25px;

            bottom: 100px;

            width: 370px;

            height: 530px;

            background: white;

            border-radius: 12px;

            box-shadow:
                0 5px 25px
                rgba(0,0,0,0.25);

            display: none;

            flex-direction: column;

            overflow: hidden;

            z-index: 9998;

        }


        #chat-header {

            background-color:
                #1c3a66;

            color: white;

            padding: 15px;

            font-size: 16px;

            font-weight: bold;

            display: flex;

            justify-content:
                space-between;

            align-items:
                center;

        }


        #chat-close {

            background: none;

            border: none;

            color: white;

            font-size: 22px;

            cursor: pointer;

        }


        #chat-body {

            flex: 1;

            padding: 15px;

            overflow-y: auto;

            background-color:
                #f4f6f8;

        }


        #chat-footer {

            display: flex;

            padding: 10px;

            border-top:
                1px solid #ddd;

            background:
                white;

            gap: 8px;

        }


        #chat-input {

            flex: 1;

            padding: 10px;

            border:
                1px solid #ccc;

            border-radius: 7px;

            outline: none;

            font-size: 14px;

        }


        #chat-input:focus {

            border-color:
                #1c3a66;

        }


        #send-btn {

            padding:
                10px 15px;

            background-color:
                #1c3a66;

            color: white;

            border: none;

            border-radius: 7px;

            cursor: pointer;

            font-weight: bold;

        }


        #send-btn:hover {

            background-color:
                #274c87;

        }


        #send-btn:disabled {

            background-color:
                #999;

            cursor: not-allowed;

        }


        .message {

            max-width: 85%;

            padding:
                10px 12px;

            margin-bottom:
                10px;

            border-radius:
                10px;

            font-size:
                14px;

            line-height:
                1.5;

            word-wrap:
                break-word;

        }


        .message.bot {

            background-color:
                white;

            border:
                1px solid #ddd;

            color:
                #333;

            margin-right:
                auto;

        }


        .message.user {

            background-color:
                #1c3a66;

            color:
                white;

            margin-left:
                auto;

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 768px) {

            .sidebar {

                position: static;

                width: 100%;

                height: auto;

                flex-direction: row;

                justify-content:
                    space-around;

                flex-wrap: wrap;

            }


            .main-content {

                margin-left: 0;

                width: 100%;

                padding: 1rem;

            }


            .profile-container {

                max-width: 100%;

            }


            #chat-container {

                right: 10px;

                bottom: 85px;

                width:
                    calc(100% - 20px);

                height: 500px;

            }


            #chat-toggle {

                right: 15px;

                bottom: 15px;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
====================================================== -->

<div class="sidebar">

    <a href="/8thsem_project/student-home/applications.php">
        Application Status
    </a>


    <a href="/8thsem_project/student-home/view_all_colleges.php">
        All Colleges
    </a>


    <a href="/8thsem_project/student-home/view_other_universities.php">
        Universities
    </a>


    <a href="/8thsem_project/student-home/profile_update.php">
        Profile
    </a>


    <a href="/8thsem_project/login/logout.php">
        Logout
    </a>

</div>


<!-- =====================================================
     MAIN CONTENT
====================================================== -->

<div class="main-content">


    <div class="page-header">

        <h1>
            Edit Profile
        </h1>

        <p>
            Update your personal information and password.
        </p>

    </div>


    <?php if ($error): ?>

        <div class="error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <?php if ($success): ?>

        <div class="success">

            <?php
            echo htmlspecialchars($success);
            ?>

        </div>

    <?php endif; ?>


    <div class="profile-container">


        <form method="POST" action="">


            <!-- FULL NAME -->

            <label for="fullname">
                Full Name
            </label>

            <input
                type="text"
                id="fullname"
                name="fullname"
                value="<?php echo htmlspecialchars($student['fullname']); ?>"
                required
            >


            <!-- GENDER -->

            <label for="gender">
                Gender
            </label>

            <select
                id="gender"
                name="gender"
                required
            >

                <option
                    value=""
                    disabled
                    <?php
                    echo empty($student['gender'])
                        ? 'selected'
                        : '';
                    ?>
                >
                    Select Gender
                </option>

                <option
                    value="Male"
                    <?php
                    echo $student['gender'] === 'Male'
                        ? 'selected'
                        : '';
                    ?>
                >
                    Male
                </option>

                <option
                    value="Female"
                    <?php
                    echo $student['gender'] === 'Female'
                        ? 'selected'
                        : '';
                    ?>
                >
                    Female
                </option>

            </select>


            <!-- EMAIL -->

            <label for="email">
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?php echo htmlspecialchars($student['email']); ?>"
                required
            >


            <!-- PASSWORD -->

            <div class="password-section">

                <h3>
                    Change Password
                </h3>

                <p class="password-note">
                    Leave these fields empty if you do not want to change your password.
                </p>


                <label for="old_password">
                    Old Password
                </label>

                <input
                    type="password"
                    id="old_password"
                    name="old_password"
                    autocomplete="current-password"
                >


                <label for="new_password">
                    New Password
                </label>

                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    autocomplete="new-password"
                >

            </div>


            <button
                type="submit"
                class="update-btn"
            >
                Update Profile
            </button>


        </form>


    </div>


</div>


<!-- =====================================================
     CHATBOT
====================================================== -->

<?php include 'chatbot_widget.php'; ?>


</body>

</html>

<?php

$conn->close();

?>