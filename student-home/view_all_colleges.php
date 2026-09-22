<?php

session_start();

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

if (isset($_GET['logout'])) {

    session_destroy();

    header("Location: /8thsem_project/login/login.html");

    exit();
}


/*
|--------------------------------------------------------------------------
| GET COLLEGES FOR JSON REQUEST
|--------------------------------------------------------------------------
|
| The JavaScript calls this same file using fetch().
| If the request asks for JSON, return college data only.
|
*/

if (
    isset($_SERVER['HTTP_ACCEPT']) &&
    strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
) {

    require_once '../db_connection/db_connection.php';

    header('Content-Type: application/json');

    $sql = "
        SELECT
            id,
            name,
            course,
            university,
            location,
            phone_number,
            scholarships,
            duration,
            description
        FROM colleges
        ORDER BY name ASC
    ";

    $result = $conn->query($sql);

    $colleges = [];

    if ($result) {

        while ($row = $result->fetch_assoc()) {

            $colleges[] = $row;

        }

    }

    echo json_encode($colleges);

    $conn->close();

    exit();
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>All Colleges</title>


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
           SEARCH
        ===================================================== */

        .search-container {

            display: flex;

            gap: 10px;

            margin-bottom: 2rem;

        }


        #search-input {

            flex: 1;

            padding: 0.7rem;

            font-size: 1rem;

            border-radius: 5px;

            border: 1px solid #ccc;

            outline: none;

        }


        #search-input:focus {

            border-color: #1c3a66;

        }


        #search-btn {

            padding:
                0.5rem 1.2rem;

            font-size: 1rem;

            background-color:
                #1c3a66;

            color: white;

            border: none;

            border-radius: 5px;

            cursor: pointer;

        }


        #search-btn:hover {

            background-color:
                #274c87;

        }


        /* =====================================================
           COLLEGE GRID
        ===================================================== */

        .grid-table {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fill,
                    minmax(300px, 1fr)
                );

            gap: 1.5rem;

        }


        /* =====================================================
           COLLEGE CARD
        ===================================================== */

        .college-card {

            background: #fff;

            border:
                1px solid #ccc;

            border-radius: 10px;

            padding: 1rem;

            box-shadow:
                0 2px 5px
                rgba(0,0,0,0.1);

        }


        .college-card h3 {

            color:
                #1c3a66;

            margin-top: 0;

            margin-bottom:
                0.8rem;

        }


        .college-card div {

            margin-bottom:
                0.4rem;

        }


        .label {

            font-weight: bold;

            color:
                #274c87;

        }


        /* =====================================================
           ACTION BUTTONS
        ===================================================== */

        .actions {

            margin-top:
                0.8rem;

            display: flex;

            justify-content:
                space-between;

            gap:
                0.5rem;

        }


        .actions button {

            flex: 1;

            padding:
                0.5rem;

            font-size:
                0.9rem;

            border: none;

            border-radius:
                6px;

            cursor: pointer;

            font-weight:
                bold;

        }


        .view-btn {

            background-color:
                #1c3a66;

            color:
                #fff;

        }


        .view-btn:hover {

            background-color:
                #274c87;

        }


        .apply-btn {

            background-color:
                #f7b733;

            color:
                #1c3a66;

        }


        .apply-btn:hover {

            background-color:
                #e6a600;

        }


        /* =====================================================
           CHATBOT BUTTON
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
           CHATBOT WINDOW
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


        /* =====================================================
           CHAT HEADER
        ===================================================== */

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


        /* =====================================================
           CHAT BODY
        ===================================================== */

        #chat-body {

            flex: 1;

            padding: 15px;

            overflow-y: auto;

            background-color:
                #f4f6f8;

        }


        /* =====================================================
           CHAT MESSAGES
        ===================================================== */

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
           CHAT FOOTER
        ===================================================== */

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
           RECOMMENDATION CARD
        ===================================================== */

        .recommendation-card {

            background:
                white;

            border:
                1px solid #d5d5d5;

            border-left:
                4px solid #1c3a66;

            border-radius:
                8px;

            padding:
                12px;

            margin-top:
                5px;

            box-shadow:
                0 2px 5px
                rgba(0,0,0,0.08);

        }


        .recommendation-card h3 {

            margin:
                0 0 8px 0;

            color:
                #1c3a66;

            font-size:
                16px;

        }


        .recommendation-card p {

            margin:
                5px 0;

            font-size:
                13px;

        }


        .match-score {

            font-weight:
                bold;

            color:
                #1c3a66;

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


            .grid-table {

                grid-template-columns:
                    1fr;

            }


            .search-container {

                flex-direction:
                    column;

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


        <div class="search-container">

            <input
                type="text"
                id="search-input"
                placeholder="Search by name, course, university, location..."
            >


            <button id="search-btn">
                Search
            </button>

        </div>


        <div
            class="grid-table"
            id="college-container"
        >

            <p>
                Loading colleges...
            </p>

        </div>

    </div>


    <!-- =====================================================
         CHATBOT BUTTON
    ====================================================== -->

    <button
        id="chat-toggle"
        title="College Recommendation Assistant"
    >
        💬
    </button>


    <!-- =====================================================
         CHATBOT WINDOW
    ====================================================== -->

    <div id="chat-container">


        <div id="chat-header">

            <span>
                College Recommendation Assistant
            </span>


            <button
                id="chat-close"
                title="Close"
            >
                ×
            </button>

        </div>


        <div id="chat-body">

            <div class="message bot">

                Hello! 👋

                <br><br>

                I can help you find suitable colleges.

                <br><br>

                What course are you interested in?

            </div>

        </div>


        <div id="chat-footer">

            <input
                type="text"
                id="chat-input"
                placeholder="Type your message..."
                autocomplete="off"
            >


            <button id="send-btn">
                Send
            </button>

        </div>

    </div>


    <!-- =====================================================
         JAVASCRIPT
    ====================================================== -->

    <script>


        /* =====================================================
           COLLEGE DATA
        ===================================================== */

        const container =
            document.getElementById(
                "college-container"
            );


        const searchInput =
            document.getElementById(
                "search-input"
            );


        const searchBtn =
            document.getElementById(
                "search-btn"
            );


        let allColleges = [];


        /* =====================================================
           ESCAPE HTML
        ===================================================== */

        function escapeHTML(text) {

            if (
                text === null ||
                text === undefined
            ) {

                return "";

            }


            const div =
                document.createElement("div");


            div.textContent = text;


            return div.innerHTML;

        }


        /* =====================================================
           RENDER COLLEGES
        ===================================================== */

        function renderColleges(data) {

            container.innerHTML = "";


            if (
                !data ||
                data.length === 0
            ) {

                container.innerHTML =
                    "<p>No colleges found.</p>";

                return;

            }


            data.forEach(
                college => {

                    const card =
                        document.createElement(
                            "div"
                        );


                    card.className =
                        "college-card";


                    card.innerHTML = `

                        <h3>
                            ${escapeHTML(
                                college.name
                            )}
                        </h3>


                        <div>

                            <span class="label">
                                Course:
                            </span>

                            ${escapeHTML(
                                college.course
                            )}

                        </div>


                        <div>

                            <span class="label">
                                University:
                            </span>

                            ${escapeHTML(
                                college.university
                            )}

                        </div>


                        <div>

                            <span class="label">
                                Location:
                            </span>

                            ${escapeHTML(
                                college.location
                            )}

                        </div>


                        <div>

                            <span class="label">
                                Phone:
                            </span>

                            ${escapeHTML(
                                college.phone_number
                            )}

                        </div>


                        <div>

                            <span class="label">
                                Scholarships:
                            </span>

                            ${escapeHTML(
                                college.scholarships
                            )}

                        </div>


                        <div>

                            <span class="label">
                                Duration:
                            </span>

                            ${escapeHTML(
                                college.duration
                            )}

                        </div>


                        <div class="actions">

                            <button
                                class="view-btn"
                                onclick="
                                    viewDetails(
                                        ${college.id}
                                    )
                                "
                            >
                                View Details
                            </button>


                            <button
                                class="apply-btn"
                                onclick="
                                    applyCollege(
                                        ${college.id}
                                    )
                                "
                            >
                                Apply Now
                            </button>

                        </div>

                    `;


                    container.appendChild(
                        card
                    );

                }
            );

        }


        /* =====================================================
           FETCH COLLEGES
        ===================================================== */

        function fetchColleges() {

            fetch(
                "all_colleges.php",
                {
                    headers: {
                        "Accept":
                            "application/json"
                    }
                }
            )

            .then(
                response => {

                    if (
                        !response.ok
                    ) {

                        throw new Error(
                            "Failed to load colleges"
                        );

                    }


                    return response.json();

                }
            )

            .then(
                data => {

                    allColleges =
                        data;

                    renderColleges(
                        allColleges
                    );

                }
            )

            .catch(
                error => {

                    console.error(
                        error
                    );


                    container.innerHTML =
                        "<p>Error loading college data.</p>";

                }
            );

        }


        /* =====================================================
           SEARCH
        ===================================================== */

        function handleSearch() {

            const keyword =
                searchInput.value
                    .toLowerCase()
                    .trim();


            const filtered =
                allColleges.filter(
                    college => {

                        const searchableText =

                            (
                                (college.name || "") +
                                " " +
                                (college.course || "") +
                                " " +
                                (college.university || "") +
                                " " +
                                (college.location || "")
                            )
                            .toLowerCase();


                        return searchableText
                            .includes(keyword);

                    }
                );


            renderColleges(
                filtered
            );

        }


        searchBtn.addEventListener(
            "click",
            handleSearch
        );


        searchInput.addEventListener(
            "keydown",
            function(event) {

                if (
                    event.key === "Enter"
                ) {

                    event.preventDefault();

                    handleSearch();

                }

            }
        );


        /* =====================================================
           VIEW COLLEGE
        ===================================================== */

        function viewDetails(id) {

            window.location.href =
                `view_college_details.php?id=${id}`;

        }


        /* =====================================================
           APPLY COLLEGE
        ===================================================== */

        function applyCollege(id) {

            window.location.href =
                `apply_college.php?college_id=${id}`;

        }


        /* LOAD COLLEGES */

        fetchColleges();



        /* =====================================================
           CHATBOT
        ===================================================== */

        const chatToggle =
            document.getElementById(
                "chat-toggle"
            );


        const chatContainer =
            document.getElementById(
                "chat-container"
            );


        const chatClose =
            document.getElementById(
                "chat-close"
            );


        const chatInput =
            document.getElementById(
                "chat-input"
            );


        const sendBtn =
            document.getElementById(
                "send-btn"
            );


        const chatBody =
            document.getElementById(
                "chat-body"
            );


        /* =====================================================
           OPEN / CLOSE CHAT
        ===================================================== */

        chatToggle.addEventListener(
            "click",
            function() {

                if (
                    chatContainer.style.display ===
                    "none" ||
                    chatContainer.style.display ===
                    ""
                ) {

                    chatContainer.style.display =
                        "flex";

                    chatInput.focus();

                } else {

                    chatContainer.style.display =
                        "none";

                }

            }
        );


        chatClose.addEventListener(
            "click",
            function() {

                chatContainer.style.display =
                    "none";

            }
        );


        /* =====================================================
           ADD CHAT MESSAGE
        ===================================================== */

        function addMessage(
            message,
            sender
        ) {

            const messageDiv =
                document.createElement(
                    "div"
                );


            messageDiv.className =
                "message " + sender;


            messageDiv.innerHTML =
                message;


            chatBody.appendChild(
                messageDiv
            );


            chatBody.scrollTop =
                chatBody.scrollHeight;

        }


        /* =====================================================
           SEND CHAT MESSAGE
        ===================================================== */

        function sendMessage() {

            const message =
                chatInput.value.trim();


            if (
                message === ""
            ) {

                return;

            }


            /* USER MESSAGE */

            addMessage(
                escapeHTML(message),
                "user"
            );


            chatInput.value = "";


            sendBtn.disabled =
                true;


            /* =================================================
               SEND TO EXISTING chatbot.php
            ================================================= */

            fetch(
                "chatbot.php",
                {
                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/x-www-form-urlencoded"
                    },

                    body:
                        "message=" +
                        encodeURIComponent(
                            message
                        )
                }
            )

            .then(
                response => {

                    if (
                        !response.ok
                    ) {

                        throw new Error(
                            "Chatbot server error"
                        );

                    }


                    return response.json();

                }
            )

            .then(
                data => {


                    /* BOT REPLY */

                    if (
                        data.reply
                    ) {

                        addMessage(
                            data.reply,
                            "bot"
                        );

                    }


                    /* =================================================
                       RECOMMENDATIONS
                    ================================================= */

                    if (
                        data.recommendations &&
                        data.recommendations.length >
                        0
                    ) {


                        data.recommendations.forEach(
                            function(college) {


                                const card =
                                    document.createElement(
                                        "div"
                                    );


                                card.className =
                                    "message bot";


                                card.innerHTML = `

                                    <div
                                        class="recommendation-card"
                                    >

                                        <h3>
                                            ${escapeHTML(
                                                college.name
                                            )}
                                        </h3>


                                        <p>

                                            <strong>
                                                Course:
                                            </strong>

                                            ${escapeHTML(
                                                college.course
                                            )}

                                        </p>


                                        <p>

                                            <strong>
                                                University:
                                            </strong>

                                            ${escapeHTML(
                                                college.university
                                            )}

                                        </p>


                                        <p>

                                            <strong>
                                                Location:
                                            </strong>

                                            ${escapeHTML(
                                                college.location
                                            )}

                                        </p>


                                        <p>

                                            <strong>
                                                Duration:
                                            </strong>

                                            ${escapeHTML(
                                                college.duration ||
                                                "N/A"
                                            )}

                                        </p>


                                        <p>

                                            <strong>
                                                Scholarship:
                                            </strong>

                                            ${escapeHTML(
                                                college.scholarships ||
                                                "N/A"
                                            )}

                                        </p>


                                        <p
                                            class="match-score"
                                        >

                                            Match Score:
                                            ${escapeHTML(
                                                String(
                                                    college.score
                                                )
                                            )}%

                                        </p>

                                    </div>

                                `;


                                chatBody.appendChild(
                                    card
                                );


                                chatBody.scrollTop =
                                    chatBody.scrollHeight;

                            }
                        );

                    }

                }
            )

            .catch(
                error => {

                    console.error(
                        "Chatbot Error:",
                        error
                    );


                    addMessage(
                        "Sorry, I could not process your message. Please try again.",
                        "bot"
                    );

                }
            )

            .finally(
                function() {

                    sendBtn.disabled =
                        false;

                    chatInput.focus();

                }
            );

        }


        /* =====================================================
           SEND BUTTON
        ===================================================== */

        sendBtn.addEventListener(
            "click",
            sendMessage
        );


        /* =====================================================
           ENTER KEY SEND
        ===================================================== */

        chatInput.addEventListener(
            "keydown",
            function(event) {

                if (
                    event.key === "Enter"
                ) {

                    event.preventDefault();

                    sendMessage();

                }

            }
        );

    </script>

</body>

</html>