<?php

session_start();


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
| JavaScript requests this same file with:
| Accept: application/json
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
         REUSABLE CHATBOT
    ====================================================== -->

    <?php include 'chatbot_widget.php'; ?>


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


        /* =====================================================
           SEARCH WITH ENTER
        ===================================================== */

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


        /* =====================================================
           LOAD COLLEGES
        ===================================================== */

        fetchColleges();

    </script>


</body>

</html>