<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db_connection/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode([
        'success' => false,
        'reply' => 'Your session has expired. Please login again.'
    ]);
    exit();
}

$studentId = (int) $_SESSION['student_id'];
$studentName = $_SESSION['fullname'] ?? 'Student';

$rawMessage = trim($_POST['message'] ?? '');

if ($rawMessage === '') {
    echo json_encode([
        'success' => false,
        'reply' => 'Please enter a message.'
    ]);
    exit();
}

$message = strtolower($rawMessage);


/*
|--------------------------------------------------------------------------
| RESTART
|--------------------------------------------------------------------------
*/

$restartWords = [
    'restart',
    'start again',
    'start over',
    'new recommendation',
    'recommend again'
];

if (in_array($message, $restartWords)) {

    unset(
        $_SESSION['chat_step'],
        $_SESSION['chat_course'],
        $_SESSION['chat_university'],
        $_SESSION['chat_location']
    );

    $_SESSION['chat_step'] = 'course';

    echo json_encode([
        'success' => true,
        'reply' =>
            'Sure ' . htmlspecialchars($studentName) .
            '! 👋 Let\'s find colleges for you.' .
            '<br><br>What course are you interested in?'
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| GREETING
|--------------------------------------------------------------------------
*/

$greetings = [
    'hi',
    'hello',
    'hey',
    'hii',
    'hiii',
    'namaste'
];

if (in_array($message, $greetings)) {

    if (!isset($_SESSION['chat_step']) || $_SESSION['chat_step'] === 'completed') {

        $_SESSION['chat_step'] = 'course';

        echo json_encode([
            'success' => true,
            'reply' =>
                'Hello ' .
                htmlspecialchars($studentName) .
                '! 👋<br><br>' .
                'How can I help you?' .
                '<br><br>' .
                'What course are you interested in?'
        ]);

        exit();
    }
}


/*
|--------------------------------------------------------------------------
| INITIAL CHAT STEP
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['chat_step'])) {

    $_SESSION['chat_step'] = 'course';

    echo json_encode([
        'success' => true,
        'reply' =>
            'Hello ' .
            htmlspecialchars($studentName) .
            '! 👋<br><br>' .
            'What course are you interested in?'
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| STEP 1 - COURSE
|--------------------------------------------------------------------------
*/

if ($_SESSION['chat_step'] === 'course') {

    $_SESSION['chat_course'] = $rawMessage;

    $_SESSION['chat_step'] = 'university';

    echo json_encode([
        'success' => true,
        'reply' =>
            'Great! 👍<br><br>' .
            'Course: <strong>' .
            htmlspecialchars($rawMessage) .
            '</strong>' .
            '<br><br>' .
            'Which university do you prefer?'
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| STEP 2 - UNIVERSITY
|--------------------------------------------------------------------------
*/

if ($_SESSION['chat_step'] === 'university') {

    $_SESSION['chat_university'] = $rawMessage;

    $_SESSION['chat_step'] = 'location';

    echo json_encode([
        'success' => true,
        'reply' =>
            'Good choice! 👍<br><br>' .
            'Which location do you prefer?'
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| STEP 3 - LOCATION
|--------------------------------------------------------------------------
*/

if ($_SESSION['chat_step'] === 'location') {

    $_SESSION['chat_location'] = $rawMessage;

    $course = $_SESSION['chat_course'] ?? '';
    $university = $_SESSION['chat_university'] ?? '';
    $location = $_SESSION['chat_location'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | SAVE PREFERENCES
    |--------------------------------------------------------------------------
    */

    $courseDB = $conn->real_escape_string($course);
    $universityDB = $conn->real_escape_string($university);
    $locationDB = $conn->real_escape_string($location);


    // Remove previous preference
    $deleteOld = "
        DELETE FROM student_preferences
        WHERE student_id = $studentId
    ";

    $conn->query($deleteOld);


    // Insert latest preference
    $insertPreference = "
        INSERT INTO student_preferences
        (
            student_id,
            preferred_course,
            preferred_university,
            preferred_location
        )
        VALUES
        (
            $studentId,
            '$courseDB',
            '$universityDB',
            '$locationDB'
        )
    ";

    $conn->query($insertPreference);


    /*
    |--------------------------------------------------------------------------
    | GET COLLEGES
    |--------------------------------------------------------------------------
    */

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
    ";

    $result = $conn->query($sql);

    $colleges = [];


    if ($result) {

        while ($row = $result->fetch_assoc()) {


            /*
            |--------------------------------------------------------------------------
            | NORMALIZE VALUES
            |--------------------------------------------------------------------------
            */

            $preferredCourse = strtolower(trim($course));
            $preferredUniversity = strtolower(trim($university));
            $preferredLocation = strtolower(trim($location));

            $collegeCourse = strtolower(
                trim($row['course'] ?? '')
            );

            $collegeUniversity = strtolower(
                trim($row['university'] ?? '')
            );

            $collegeLocation = strtolower(
                trim($row['location'] ?? '')
            );


            /*
            |--------------------------------------------------------------------------
            | COURSE MATCH - 40%
            |--------------------------------------------------------------------------
            */

            $courseMatch = 0;

            if (
                strpos($collegeCourse, $preferredCourse) !== false ||
                strpos($preferredCourse, $collegeCourse) !== false
            ) {
                $courseMatch = 1;
            }


            /*
            |--------------------------------------------------------------------------
            | UNIVERSITY MATCH - 30%
            |--------------------------------------------------------------------------
            */

            $universityMatch = 0;

            if (
                strpos($collegeUniversity, $preferredUniversity) !== false ||
                strpos($preferredUniversity, $collegeUniversity) !== false
            ) {
                $universityMatch = 1;
            }


            /*
            |--------------------------------------------------------------------------
            | LOCATION MATCH - 20%
            |--------------------------------------------------------------------------
            */

            $locationMatch = 0;

            if (
                strpos($collegeLocation, $preferredLocation) !== false ||
                strpos($preferredLocation, $collegeLocation) !== false
            ) {
                $locationMatch = 1;
            }


            /*
            |--------------------------------------------------------------------------
            | TEXT DATA
            |--------------------------------------------------------------------------
            */

            $collegeText = strtolower(
                ($row['name'] ?? '') . ' ' .
                ($row['course'] ?? '') . ' ' .
                ($row['university'] ?? '') . ' ' .
                ($row['location'] ?? '') . ' ' .
                ($row['description'] ?? '') . ' ' .
                ($row['scholarships'] ?? '')
            );

            $preferenceText =
                $preferredCourse . ' ' .
                $preferredUniversity . ' ' .
                $preferredLocation;


            /*
            |--------------------------------------------------------------------------
            | TOKENIZE
            |--------------------------------------------------------------------------
            */

            $queryWords = preg_split(
                '/[^a-z0-9]+/i',
                $preferenceText,
                -1,
                PREG_SPLIT_NO_EMPTY
            );

            $collegeWords = preg_split(
                '/[^a-z0-9]+/i',
                $collegeText,
                -1,
                PREG_SPLIT_NO_EMPTY
            );


            /*
            |--------------------------------------------------------------------------
            | TERM FREQUENCY
            |--------------------------------------------------------------------------
            */

            $queryVector = [];
            $collegeVector = [];

            foreach ($queryWords as $word) {

                $queryVector[$word] =
                    ($queryVector[$word] ?? 0) + 1;
            }

            foreach ($collegeWords as $word) {

                $collegeVector[$word] =
                    ($collegeVector[$word] ?? 0) + 1;
            }


            /*
            |--------------------------------------------------------------------------
            | COSINE SIMILARITY
            |--------------------------------------------------------------------------
            */

            $allWords = array_unique(
                array_merge(
                    array_keys($queryVector),
                    array_keys($collegeVector)
                )
            );

            $dotProduct = 0;
            $queryMagnitude = 0;
            $collegeMagnitude = 0;

            foreach ($allWords as $word) {

                $q = $queryVector[$word] ?? 0;
                $c = $collegeVector[$word] ?? 0;

                $dotProduct += $q * $c;

                $queryMagnitude += $q * $q;

                $collegeMagnitude += $c * $c;
            }


            if (
                $queryMagnitude > 0 &&
                $collegeMagnitude > 0
            ) {

                $cosineSimilarity =
                    $dotProduct /
                    (
                        sqrt($queryMagnitude) *
                        sqrt($collegeMagnitude)
                    );

            } else {

                $cosineSimilarity = 0;
            }


            /*
            |--------------------------------------------------------------------------
            | HYBRID ALGORITHM
            |--------------------------------------------------------------------------
            |
            | Course       = 40%
            | University   = 30%
            | Location     = 20%
            | Cosine       = 10%
            |
            */

            $hybridScore =
                ($courseMatch * 0.40) +
                ($universityMatch * 0.30) +
                ($locationMatch * 0.20) +
                ($cosineSimilarity * 0.10);


            /*
            |--------------------------------------------------------------------------
            | CONVERT TO PERCENTAGE
            |--------------------------------------------------------------------------
            */

            $row['score'] = round(
                $hybridScore * 100,
                2
            );


            /*
            |--------------------------------------------------------------------------
            | MINIMUM RECOMMENDATION THRESHOLD
            |--------------------------------------------------------------------------
            |
            | Only colleges with 50% or higher
            | will be considered suitable.
            |
            */

            if ($row['score'] >= 50) {

                $colleges[] = $row;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SORT BY SCORE
    |--------------------------------------------------------------------------
    */

    usort(
        $colleges,
        function ($a, $b) {

            if ($a['score'] == $b['score']) {
                return 0;
            }

            return ($a['score'] < $b['score']) ? 1 : -1;
        }
    );


    /*
    |--------------------------------------------------------------------------
    | TOP 5 RECOMMENDATIONS
    |--------------------------------------------------------------------------
    |
    | Because only 50%+ colleges were added above,
    | all recommendations here are 50% or higher.
    |
    */

    $recommendations =
        array_slice($colleges, 0, 5);


    /*
    |--------------------------------------------------------------------------
    | CHAT COMPLETED
    |--------------------------------------------------------------------------
    */

    $_SESSION['chat_step'] = 'completed';


    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    if (count($recommendations) > 0) {

        $reply =
            'Thank you, ' .
            htmlspecialchars($studentName) .
            '! 🎓' .
            '<br><br>' .
            'I found colleges with a match score of <strong>50% or higher</strong>.' .
            '<br><br>' .
            '<strong>Course:</strong> ' .
            htmlspecialchars($course) .
            '<br>' .
            '<strong>University:</strong> ' .
            htmlspecialchars($university) .
            '<br>' .
            '<strong>Location:</strong> ' .
            htmlspecialchars($location) .
            '<br><br>' .
            'Here are the suitable colleges:';

    } else {

        $reply =
            'Sorry ' .
            htmlspecialchars($studentName) .
            ', there is no college suitable for you based on your current preferences.' .
            '<br><br>' .
            'No college achieved the minimum <strong>50% match score</strong>.' .
            '<br><br>' .
            'You can type <strong>restart</strong> to try different preferences.';
    }


    echo json_encode([
        'success' => true,
        'reply' => $reply,
        'recommendations' => $recommendations
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| AFTER RECOMMENDATION
|--------------------------------------------------------------------------
*/

if ($_SESSION['chat_step'] === 'completed') {

    echo json_encode([
        'success' => true,
        'reply' =>
            'Your recommendation has already been generated. 😊' .
            '<br><br>' .
            'Type <strong>restart</strong> to search for colleges again.'
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| DEFAULT
|--------------------------------------------------------------------------
*/

echo json_encode([
    'success' => true,
    'reply' =>
        'I can help you find suitable colleges. 😊<br><br>' .
        'Type <strong>restart</strong> to start again.'
]);

$conn->close();

?>