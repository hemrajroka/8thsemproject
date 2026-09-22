<?php

session_start();

require('../db_connection/db_connection.php');


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


/* =====================================================
   GET APPLICATIONS
===================================================== */

$sql = "
    SELECT
        a.id,
        c.name AS college_name,
        a.course,
        a.applied_at,
        a.status
    FROM applications a
    INNER JOIN colleges c
        ON a.college_id = c.id
    WHERE a.student_id = ?
    ORDER BY a.applied_at DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database query error: " . $conn->error);
}

$stmt->bind_param("i", $studentID);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Application Status</title>


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
                2px 0 5px rgba(0, 0, 0, 0.1);

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
           APPLICATION CONTAINER
        ===================================================== */

        .application-container {

            background: #fff;

            border:
                1px solid #ccc;

            border-radius: 10px;

            padding: 1.5rem;

            box-shadow:
                0 2px 5px
                rgba(0,0,0,0.1);
        }


        /* =====================================================
           APPLICATION CARD
        ===================================================== */

        .application-card {

            background: #fff;

            border:
                1px solid #ccc;

            border-radius: 10px;

            padding: 1.2rem;

            margin-bottom: 1rem;

            box-shadow:
                0 2px 5px
                rgba(0,0,0,0.08);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .application-card:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 4px 10px
                rgba(0,0,0,0.12);
        }


        .application-card:last-child {

            margin-bottom: 0;
        }


        /* =====================================================
           COLLEGE NAME
        ===================================================== */

        .application-card h3 {

            color: #1c3a66;

            margin-top: 0;

            margin-bottom: 1rem;

            font-size: 20px;
        }


        /* =====================================================
           DETAILS
        ===================================================== */

        .application-detail {

            margin-bottom: 0.5rem;

            font-size: 15px;
        }


        .label {

            font-weight: bold;

            color: #274c87;
        }


        /* =====================================================
           STATUS
        ===================================================== */

        .status {

            display: inline-block;

            padding:
                0.4rem 0.8rem;

            border-radius: 6px;

            font-size: 0.9rem;

            font-weight: bold;
        }


        .status-approved {

            background-color: #d4edda;

            color: #155724;
        }


        .status-rejected {

            background-color: #f8d7da;

            color: #721c24;
        }


        .status-pending {

            background-color: #fff3cd;

            color: #856404;
        }


        .status-default {

            background-color: #e2e3e5;

            color: #383d41;
        }


        /* =====================================================
           NO APPLICATIONS
        ===================================================== */

        .no-applications {

            background: #fff;

            border:
                1px solid #ccc;

            border-radius: 10px;

            padding: 2rem;

            text-align: center;

            box-shadow:
                0 2px 5px
                rgba(0,0,0,0.1);
        }


        .no-applications h3 {

            color: #1c3a66;

            margin-top: 0;
        }


        .no-applications p {

            color: #666;

            margin-bottom: 0;
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


        /* =====================================================
           CHAT WINDOW
        ===================================================== */

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


        #chat-footer {

            display: flex;

            padding: 10px;

            border-top:
                1px solid #ddd;

            background:
                white;

            gap:
                8px;
        }


        #chat-input {

            flex: 1;

            padding:
                10px;

            border:
                1px solid #ccc;

            border-radius:
                7px;

            outline:
                none;

            font-size:
                14px;
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

            color:
                white;

            border:
                none;

            border-radius:
                7px;

            cursor:
                pointer;

            font-weight:
                bold;
        }


        #send-btn:hover {

            background-color:
                #274c87;
        }


        #send-btn:disabled {

            background-color:
                #999;

            cursor:
                not-allowed;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 768px) {

            .sidebar {

                position:
                    static;

                width:
                    100%;

                height:
                    auto;

                flex-direction:
                    row;

                justify-content:
                    space-around;

                flex-wrap:
                    wrap;
            }


            .main-content {

                margin-left:
                    0;

                width:
                    100%;

                padding:
                    1rem;
            }


            .application-container {

                padding:
                    1rem;
            }


            #chat-container {

                right:
                    10px;

                bottom:
                    85px;

                width:
                    calc(100% - 20px);

                height:
                    500px;
            }


            #chat-toggle {

                right:
                    15px;

                bottom:
                    15px;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
====================================================== -->

<div class="sidebar">

    <a
        href="/8thsem_project/student-home/applications.php"
    >
        Application Status
    </a>


    <a
        href="/8thsem_project/student-home/view_all_colleges.php"
    >
        All Colleges
    </a>


    <a
        href="/8thsem_project/student-home/view_other_universities.php"
    >
        Universities
    </a>


    <a
        href="/8thsem_project/student-home/profile_update.php"
    >
        Profile
    </a>


    <a
        href="/8thsem_project/login/logout.php"
    >
        Logout
    </a>

</div>


<!-- =====================================================
     MAIN CONTENT
====================================================== -->

<div class="main-content">


    <div class="page-header">

        <h1>
            Application Status
        </h1>

        <p>
            View the status of your college applications.
        </p>

    </div>


    <?php if ($result->num_rows > 0): ?>


        <div class="application-container">


            <?php while ($row = $result->fetch_assoc()): ?>

                <?php

                $status =
                    $row['status']
                    ?? 'Pending';

                $statusLower =
                    strtolower(
                        trim($status)
                    );


                if (
                    $statusLower ===
                    'approved'
                ) {

                    $statusClass =
                        'status-approved';

                } elseif (
                    $statusLower ===
                    'rejected'
                ) {

                    $statusClass =
                        'status-rejected';

                } elseif (
                    $statusLower ===
                    'pending'
                ) {

                    $statusClass =
                        'status-pending';

                } else {

                    $statusClass =
                        'status-default';

                }

                ?>


                <div class="application-card">


                    <h3>

                        <?php
                        echo htmlspecialchars(
                            $row['college_name']
                        );
                        ?>

                    </h3>


                    <div class="application-detail">

                        <span class="label">
                            Course:
                        </span>

                        <?php
                        echo htmlspecialchars(
                            $row['course']
                        );
                        ?>

                    </div>


                    <div class="application-detail">

                        <span class="label">
                            Applied Date:
                        </span>

                        <?php
                        echo htmlspecialchars(
                            $row['applied_at']
                        );
                        ?>

                    </div>


                    <div class="application-detail">

                        <span class="label">
                            Status:
                        </span>

                        <span
                            class="status <?php echo $statusClass; ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $status
                            );
                            ?>

                        </span>

                    </div>


                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <div class="no-applications">

            <h3>
                No Applications Found
            </h3>

            <p>
                You have not submitted any college applications yet.
            </p>

        </div>


    <?php endif; ?>


</div>


<!-- =====================================================
     CHATBOT
====================================================== -->

<?php include 'chatbot_widget.php'; ?>


</body>

</html>


<?php

$stmt->close();

$conn->close();

?>