```php
<?php

session_start();

require('../db_connection/db_connection.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


/* =========================================================
   SESSION CHECK
========================================================= */

if (isset($_SESSION['student_id'])) {

    $studentID = (int) $_SESSION['student_id'];

} elseif (isset($_SESSION['studentID'])) {

    $studentID = (int) $_SESSION['studentID'];

    $_SESSION['student_id'] = $studentID;

} else {

    header("Location: /8thsem_project/login/login.html");
    exit();

}


/* =========================================================
   SELECTED UNIVERSITY
========================================================= */

$selectedUniversity = trim($_GET['university'] ?? '');

$result = null;

if (!empty($selectedUniversity)) {

    $stmt = $conn->prepare(
        "SELECT *
         FROM colleges
         WHERE university = ?
         ORDER BY name ASC"
    );

    if ($stmt) {

        $stmt->bind_param(
            "s",
            $selectedUniversity
        );

        $stmt->execute();

        $result = $stmt->get_result();
    }
}


/* =========================================================
   UNIVERSITY LOGOS
========================================================= */

$universities = [

    'Tribhuvan University' =>
        'logo/Tu.png',

    'Pokhara University' =>
        'logo/PU.png',

    'Kathmandu University' =>
        'logo/ku.png',

    'Purbanchal University' =>
        'logo/prubhanchal.png',

];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Universities</title>


<style>

/* =========================================================
   GLOBAL
========================================================= */

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family:
        "Segoe UI",
        Tahoma,
        Geneva,
        Verdana,
        sans-serif;

    background: #f4f6f8;

    color: #333;
}


/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {

    position: fixed;

    top: 0;
    left: 0;

    width: 220px;
    height: 100vh;

    background: #1c3a66;

    padding-top: 60px;

    display: flex;

    flex-direction: column;

    z-index: 1000;
}


.sidebar a {

    text-decoration: none;

    color: #f7b733;

    font-weight: 500;

    padding: 0.75rem 1rem;

    display: block;

    transition:
        background-color 0.2s ease,
        color 0.2s ease;
}


.sidebar a:hover {

    background-color: #274c87;

    color: #ffffff;
}


/* =========================================================
   MAIN CONTENT
========================================================= */

.main-content {

    margin-left: 220px;

    padding: 2rem;

    min-height: 100vh;
}


/* =========================================================
   TOP BAR
========================================================= */

.top-bar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 25px;
}


.top-bar h2 {

    margin: 0;

    color: #1c3a66;
}


.back-button {

    display: inline-block;

    background: #1c3a66;

    color: #ffffff;

    padding: 9px 16px;

    border-radius: 6px;

    text-decoration: none;

    font-size: 14px;

    transition:
        background 0.2s ease;
}


.back-button:hover {

    background: #274c87;
}


/* =========================================================
   UNIVERSITY CARDS
========================================================= */

.university-cards {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(220px, 1fr)
        );

    gap: 20px;

    margin-top: 20px;
}


.university-card {

    background: #ffffff;

    border: 1px solid #ddd;

    border-radius: 10px;

    padding: 15px;

    text-align: center;

    box-shadow:
        0 2px 6px rgba(
            0,
            0,
            0,
            0.08
        );

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}


.university-card:hover {

    transform:
        translateY(-3px);

    box-shadow:
        0 5px 12px rgba(
            0,
            0,
            0,
            0.12
        );
}


.university-card a {

    text-decoration: none;

    color: inherit;
}


.university-card img {

    width: 100%;

    height: 130px;

    object-fit: contain;

    padding: 10px;
}


.university-card p {

    margin:
        10px 0 5px;

    font-weight: 600;

    color: #1c3a66;

    font-size: 16px;
}


/* =========================================================
   SELECTED UNIVERSITY
========================================================= */

.selected-title {

    color: #1c3a66;

    margin-top: 10px;

    margin-bottom: 20px;
}


/* =========================================================
   COLLEGE CARDS
========================================================= */

.college-row {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(300px, 1fr)
        );

    gap: 20px;
}


.college-card {

    background: #ffffff;

    border: 1px solid #ccc;

    border-radius: 10px;

    padding: 20px;

    box-shadow:
        0 2px 6px rgba(
            0,
            0,
            0,
            0.08
        );
}


.college-card h3 {

    margin-top: 0;

    margin-bottom: 15px;

    color: #1c3a66;
}


.college-card p {

    margin: 8px 0;

    line-height: 1.5;
}


.college-card strong {

    color: #1c3a66;
}


/* =========================================================
   BUTTONS
========================================================= */

.actions {

    margin-top: 18px;
}


.actions a {

    display: inline-block;

    text-decoration: none;

    padding: 8px 14px;

    border-radius: 6px;

    font-size: 14px;

    margin-right: 8px;

    margin-top: 5px;
}


.view-btn {

    background: #1c3a66;

    color: #ffffff;
}


.view-btn:hover {

    background: #274c87;
}


.apply-btn {

    background: #f7b733;

    color: #1c3a66;

    font-weight: 600;
}


.apply-btn:hover {

    background: #e6a600;
}


/* =========================================================
   NO RESULTS
========================================================= */

.no-results {

    background: #ffffff;

    padding: 20px;

    border-radius: 10px;

    border: 1px solid #ddd;

    color: #666;
}


/* =========================================================
   CHATBOT BUTTON
========================================================= */

#chat-toggle {

    position: fixed;

    right: 25px;
    bottom: 25px;

    width: 60px;
    height: 60px;

    background: #1c3a66;

    color: #f7b733;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 25px;

    cursor: pointer;

    box-shadow:
        0 4px 12px rgba(
            0,
            0,
            0,
            0.25
        );

    z-index: 9999;

    transition:
        background 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease;
}


#chat-toggle:hover {

    background: #274c87;

    color: #ffffff;

    transform:
        translateY(-3px);
}


/* =========================================================
   CHAT WINDOW
========================================================= */

#chat-container {

    position: fixed;

    right: 25px;
    bottom: 95px;

    width: 370px;
    height: 520px;

    background: #ffffff;

    border: 1px solid #ddd;

    border-radius: 10px;

    display: none;

    flex-direction: column;

    overflow: hidden;

    box-shadow:
        0 5px 20px rgba(
            0,
            0,
            0,
            0.20
        );

    z-index: 9999;
}


/* =========================================================
   CHAT HEADER
========================================================= */

#chat-header {

    background: #1c3a66;

    color: #ffffff;

    padding: 15px;

    text-align: center;

    font-size: 16px;

    font-weight: 600;

    border-bottom:
        3px solid #f7b733;
}


/* =========================================================
   CHAT BODY
========================================================= */

#chat-body {

    flex: 1;

    overflow-y: auto;

    padding: 15px;

    background: #f4f6f8;
}


/* =========================================================
   USER MESSAGE
========================================================= */

.chat-user {

    max-width: 85%;

    margin-left: auto;

    margin-bottom: 10px;

    padding: 10px 12px;

    background: #dce9ff;

    color: #333;

    border-radius:
        10px 10px 2px 10px;

    font-size: 14px;

    line-height: 1.5;

    word-wrap: break-word;
}


/* =========================================================
   BOT MESSAGE
========================================================= */

.chat-bot {

    max-width: 90%;

    margin-right: auto;

    margin-bottom: 10px;

    padding: 10px 12px;

    background: #ffffff;

    color: #333;

    border: 1px solid #ddd;

    border-radius:
        10px 10px 10px 2px;

    font-size: 14px;

    line-height: 1.5;

    box-shadow:
        0 2px 5px rgba(
            0,
            0,
            0,
            0.05
        );
}


/* =========================================================
   RECOMMENDATION CARD
========================================================= */

.recommendation-card {

    background: #ffffff;

    border: 1px solid #ddd;

    border-left:
        4px solid #f7b733;

    border-radius: 8px;

    padding: 12px;

    margin-top: 8px;

    margin-bottom: 10px;

    box-shadow:
        0 2px 6px rgba(
            0,
            0,
            0,
            0.08
        );

    font-size: 13px;
}


.recommendation-card strong {

    color: #1c3a66;
}


.recommendation-card small {

    color: #555;

    line-height: 1.6;
}


/* =========================================================
   CHAT FOOTER
========================================================= */

#chat-footer {

    display: flex;

    background: #ffffff;

    border-top: 1px solid #ddd;

    padding: 8px;
}


/* =========================================================
   CHAT INPUT
========================================================= */

#chat-input {

    flex: 1;

    padding: 11px 12px;

    border: 1px solid #ddd;

    border-radius: 6px;

    outline: none;

    font-family:
        "Segoe UI",
        Tahoma,
        Geneva,
        Verdana,
        sans-serif;

    font-size: 14px;
}


#chat-input:focus {

    border-color: #1c3a66;

    box-shadow:
        0 0 0 2px rgba(
            28,
            58,
            102,
            0.10
        );
}


/* =========================================================
   SEND BUTTON
========================================================= */

#send-btn {

    margin-left: 7px;

    padding: 0 16px;

    border: none;

    border-radius: 6px;

    background: #f7b733;

    color: #1c3a66;

    font-weight: 600;

    cursor: pointer;

    transition:
        background 0.2s ease;
}


#send-btn:hover {

    background: #e6a600;
}


#send-btn:disabled {

    opacity: 0.6;

    cursor: not-allowed;
}


/* =========================================================
   CHAT SCROLLBAR
========================================================= */

#chat-body::-webkit-scrollbar {

    width: 6px;
}


#chat-body::-webkit-scrollbar-track {

    background: #f4f6f8;
}


#chat-body::-webkit-scrollbar-thumb {

    background: #c5c5c5;

    border-radius: 10px;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 768px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

        padding: 10px 0;

        flex-direction: row;

        overflow-x: auto;
    }


    .sidebar a {

        flex: 1;

        text-align: center;

        padding: 10px;

        white-space: nowrap;

        font-size: 14px;
    }


    .main-content {

        margin-left: 0;

        padding: 1rem;
    }


    .top-bar {

        flex-direction: column;

        align-items: flex-start;
    }


    .university-cards {

        grid-template-columns: 1fr;
    }


    .college-row {

        grid-template-columns: 1fr;
    }


    #chat-container {

        right: 15px;

        bottom: 85px;

        width:
            calc(100% - 30px);

        height: 70vh;
    }


    #chat-toggle {

        right: 15px;

        bottom: 15px;
    }

}


/* =========================================================
   VERY SMALL SCREEN
========================================================= */

@media (max-width: 400px) {

    #chat-container {

        right: 10px;

        width:
            calc(100% - 20px);

        height: 75vh;
    }

}

</style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

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


<!-- =========================================================
     MAIN CONTENT
========================================================= -->

<div class="main-content">


    <!-- TOP BAR -->

    <div class="top-bar">

        <h2>
            Browse Colleges by University
        </h2>


        <?php if (!empty($selectedUniversity)): ?>

            <a
                class="back-button"
                href="/8thsem_project/student-home/view_other_universities.php"
            >
                ← Back to Universities
            </a>

        <?php else: ?>

            <a
                class="back-button"
                href="/8thsem_project/student-home/view_all_colleges.php"
            >
                ← Back to All Colleges
            </a>

        <?php endif; ?>

    </div>


    <?php if (empty($selectedUniversity)): ?>


        <!-- =================================================
             UNIVERSITY SELECTION
        ================================================== -->

        <div class="university-cards">

            <?php foreach ($universities as $name => $imgPath): ?>

                <div class="university-card">

                    <a
                        href="?university=<?= urlencode($name) ?>"
                    >

                        <img
                            src="<?= htmlspecialchars($imgPath) ?>"
                            alt="<?= htmlspecialchars($name) ?>"
                        >

                        <p>
                            <?= htmlspecialchars($name) ?>
                        </p>

                    </a>

                </div>

            <?php endforeach; ?>

        </div>


    <?php else: ?>


        <!-- =================================================
             SELECTED UNIVERSITY
        ================================================== -->

        <h3 class="selected-title">

            Colleges under:

            <?= htmlspecialchars($selectedUniversity) ?>

        </h3>


        <?php if ($result && $result->num_rows > 0): ?>


            <div class="college-row">


                <?php while ($college = $result->fetch_assoc()): ?>


                    <div class="college-card">


                        <h3>

                            <?= htmlspecialchars(
                                $college['name']
                            ) ?>

                        </h3>


                        <p>

                            <strong>
                                Course:
                            </strong>

                            <?= htmlspecialchars(
                                $college['course']
                            ) ?>

                        </p>


                        <p>

                            <strong>
                                University:
                            </strong>

                            <?= htmlspecialchars(
                                $college['university']
                            ) ?>

                        </p>


                        <p>

                            <strong>
                                Location:
                            </strong>

                            <?= htmlspecialchars(
                                $college['location']
                            ) ?>

                        </p>


                        <p>

                            <strong>
                                Phone:
                            </strong>

                            <?= htmlspecialchars(
                                $college['phone_number']
                            ) ?>

                        </p>


                        <p>

                            <strong>
                                Scholarships:
                            </strong>

                            <?= htmlspecialchars(
                                $college['scholarships']
                            ) ?>

                        </p>


                        <p>

                            <strong>
                                Description:
                            </strong>

                            <?= htmlspecialchars(
                                $college['description']
                            ) ?>

                        </p>


                        <div class="actions">


                            <a
                                class="view-btn"
                                href="view_college_details.php?id=<?= (int)$college['id'] ?>"
                            >
                                View Details
                            </a>


                            <a
                                class="apply-btn"
                                href="apply_college.php?college_id=<?= (int)$college['id'] ?>"
                            >
                                Apply
                            </a>


                        </div>


                    </div>


                <?php endwhile; ?>


            </div>


        <?php else: ?>


            <div class="no-results">

                No colleges found for this university.

            </div>


        <?php endif; ?>


    <?php endif; ?>


</div>


<!-- =========================================================
     CHATBOT
========================================================= -->

<div
    id="chat-toggle"
    title="College Assistant"
>
    💬
</div>


<div id="chat-container">


    <div id="chat-header">

        College Assistant

    </div>


    <div id="chat-body">

        <div class="chat-bot">

            <b>College Assistant:</b><br>

            Hello! How can I help you today?

        </div>

    </div>


    <div id="chat-footer">


        <input
            type="text"
            id="chat-input"
            placeholder="Ask me about colleges..."
            autocomplete="off"
        >


        <button
            type="button"
            id="send-btn"
        >
            Send
        </button>


    </div>


</div>


<script>

/* =========================================================
   CHATBOT ELEMENTS
========================================================= */

const toggle =
    document.getElementById("chat-toggle");

const container =
    document.getElementById("chat-container");

const input =
    document.getElementById("chat-input");

const sendButton =
    document.getElementById("send-btn");

const body =
    document.getElementById("chat-body");


/* =========================================================
   OPEN / CLOSE CHATBOT
========================================================= */

toggle.addEventListener(
    "click",
    function () {

        if (
            container.style.display === "flex"
        ) {

            container.style.display =
                "none";

        } else {

            container.style.display =
                "flex";

            setTimeout(
                function () {

                    input.focus();

                },
                100
            );

        }

    }
);


/* =========================================================
   SEND MESSAGE
========================================================= */

function sendMessage() {

    const msg =
        input.value.trim();


    if (msg === "") {
        return;
    }


    /* USER MESSAGE */

    const userMessage =
        document.createElement("div");

    userMessage.className =
        "chat-user";

    userMessage.innerHTML =
        "<b>You:</b> " +
        escapeHtml(msg);

    body.appendChild(
        userMessage
    );


    /* CLEAR INPUT */

    input.value = "";


    /* DISABLE SEND */

    sendButton.disabled =
        true;

    sendButton.innerText =
        "Sending...";


    body.scrollTop =
        body.scrollHeight;


    /* =====================================================
       SEND TO CHATBOT PHP
    ===================================================== */

    fetch("chatbot.php", {

        method: "POST",

        headers: {

            "Content-Type":
                "application/x-www-form-urlencoded"

        },

        body:
            "message=" +
            encodeURIComponent(msg)

    })


    .then(
        function (response) {

            if (!response.ok) {

                throw new Error(
                    "Server error: " +
                    response.status
                );

            }

            return response.json();

        }
    )


    .then(
        function (data) {


            /* BOT MESSAGE */

            const botMessage =
                document.createElement("div");

            botMessage.className =
                "chat-bot";


            const reply =
                data.reply ||
                "Sorry, I couldn't understand that.";


            botMessage.innerHTML =
                "<b>College Assistant:</b><br>" +
                reply;


            body.appendChild(
                botMessage
            );


            /* =================================================
               RECOMMENDATION CARDS
            ================================================== */

            if (
                data.recommendations &&
                Array.isArray(
                    data.recommendations
                )
            ) {


                data.recommendations.forEach(
                    function (college) {


                        const card =
                            document.createElement(
                                "div"
                            );


                        card.className =
                            "recommendation-card";


                        const name =
                            college.name ||
                            "College";


                        const course =
                            college.course ||
                            "N/A";


                        const university =
                            college.university ||
                            "N/A";


                        const percentage =
                            college.match_percentage ??
                            0;


                        card.innerHTML = `

                            <strong>
                                ${escapeHtml(name)}
                            </strong>

                            <br>

                            <small>
                                Course:
                                ${escapeHtml(course)}
                            </small>

                            <br>

                            <small>
                                University:
                                ${escapeHtml(university)}
                            </small>

                            <br>

                            <strong>
                                Match:
                                ${percentage}%
                            </strong>

                        `;


                        body.appendChild(
                            card
                        );


                    }
                );

            }


            body.scrollTop =
                body.scrollHeight;

        }
    )


    /* =====================================================
       ERROR
    ===================================================== */

    .catch(
        function (error) {

            console.error(error);


            const errorMessage =
                document.createElement(
                    "div"
                );


            errorMessage.className =
                "chat-bot";


            errorMessage.innerHTML =
                "<b>College Assistant:</b><br>" +
                "Sorry, something went wrong. Please try again.";


            body.appendChild(
                errorMessage
            );


            body.scrollTop =
                body.scrollHeight;

        }
    )


    /* =====================================================
       FINISH
    ===================================================== */

    .finally(
        function () {

            sendButton.disabled =
                false;

            sendButton.innerText =
                "Send";

            input.focus();

        }
    );

}


/* =========================================================
   SEND BUTTON
========================================================= */

sendButton.addEventListener(
    "click",
    sendMessage
);


/* =========================================================
   ENTER KEY
========================================================= */

input.addEventListener(
    "keydown",
    function (event) {

        if (event.key === "Enter") {

            event.preventDefault();

            sendMessage();

        }

    }
);


/* =========================================================
   ESCAPE HTML
========================================================= */

function escapeHtml(text) {

    const div =
        document.createElement("div");

    div.textContent =
        text;

    return div.innerHTML;

}

</script>


<?php

/* =========================================================
   CLOSE DATABASE
========================================================= */

if (isset($stmt) && $stmt) {

    $stmt->close();

}

$conn->close();

?>

</body>

</html>
```
