<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();

session_start();
header('Content-Type: application/json');

require('../db_connection/db_connection.php');

if (!isset($_SESSION['studentID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$studentID = $_SESSION['studentID'];

// Fetch student preferences
$prefStmt = $conn->prepare("SELECT university, course FROM students WHERE id = ?");
$prefStmt->bind_param("i", $studentID);
$prefStmt->execute();
$prefResult = $prefStmt->get_result();

if ($prefResult->num_rows === 0) {
    echo json_encode([]);
    exit();
}

$studentPref = $prefResult->fetch_assoc();
$studentUniversity = strtolower(trim($studentPref['university'] ?? ''));
$studentCourse = strtolower(trim($studentPref['course'] ?? ''));

$collegeResult = $conn->query("SELECT * FROM colleges");
if (!$collegeResult) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching colleges: ' . $conn->error]);
    exit();
}

$colleges = [];
while ($row = $collegeResult->fetch_assoc()) {
    $colleges[] = $row;
}

// Helper: tokenize and normalize text
function tokenize($text) {
    return str_word_count(strtolower($text), 1);
}

// Calculate term frequency (TF) for a list of tokens
function termFrequency($tokens) {
    $tf = [];
    $count = count($tokens);
    foreach ($tokens as $token) {
        $tf[$token] = ($tf[$token] ?? 0) + 1;
    }
    foreach ($tf as $token => $freq) {
        $tf[$token] = $freq / $count;
    }
    return $tf;
}

// Calculate inverse document frequency (IDF) for all docs
function inverseDocumentFrequency($documents) {
    $idf = [];
    $totalDocs = count($documents);
    foreach ($documents as $doc) {
        $uniqueTokens = array_unique($doc);
        foreach ($uniqueTokens as $token) {
            $idf[$token] = ($idf[$token] ?? 0) + 1;
        }
    }
    foreach ($idf as $token => $docCount) {
        $idf[$token] = log($totalDocs / $docCount);
    }
    return $idf;
}

// Calculate TF-IDF vector for tokens
function tfIdfVector($tokens, $idf) {
    $tf = termFrequency($tokens);
    $tfidf = [];
    foreach ($tf as $token => $tfVal) {
        $tfidf[$token] = $tfVal * ($idf[$token] ?? 0);
    }
    return $tfidf;
}

// Cosine similarity between two vectors
function cosineSimilarity($vec1, $vec2) {
    if (empty($vec1) || empty($vec2)) return 0;

    $dotProduct = 0;
    $mag1 = 0;
    $mag2 = 0;

    $allTokens = array_unique(array_merge(array_keys($vec1), array_keys($vec2)));
    foreach ($allTokens as $token) {
        $dotProduct += ($vec1[$token] ?? 0) * ($vec2[$token] ?? 0);
        $mag1 += ($vec1[$token] ?? 0) ** 2;
        $mag2 += ($vec2[$token] ?? 0) ** 2;
    }
    return ($mag1 && $mag2) ? $dotProduct / (sqrt($mag1) * sqrt($mag2)) : 0;
}

// Weights for each feature
$weights = [
    'course' => 0.7,
    'university' => 0.3,
];

// Build documents for TF-IDF: separate docs for course and university fields
$allCourseDocs = [];
$allUniversityDocs = [];

foreach ($colleges as $college) {
    $allCourseDocs[] = tokenize($college['course'] ?? '');
    $allUniversityDocs[] = tokenize($college['university'] ?? '');
}
// Add student docs too for IDF calculation
$studentCourseTokens = tokenize($studentCourse);
$studentUniversityTokens = tokenize($studentUniversity);
$allCourseDocs[] = $studentCourseTokens;
$allUniversityDocs[] = $studentUniversityTokens;

// Calculate IDF for course and university separately
$idfCourse = inverseDocumentFrequency($allCourseDocs);
$idfUniversity = inverseDocumentFrequency($allUniversityDocs);

// Calculate TF-IDF vector for student features
$studentCourseVector = tfIdfVector($studentCourseTokens, $idfCourse);
$studentUniversityVector = tfIdfVector($studentUniversityTokens, $idfUniversity);

// Calculate similarity with each college
$recommendations = [];
foreach ($colleges as $college) {
    $collegeCourseTokens = tokenize($college['course'] ?? '');
    $collegeUniversityTokens = tokenize($college['university'] ?? '');

    $collegeCourseVector = tfIdfVector($collegeCourseTokens, $idfCourse);
    $collegeUniversityVector = tfIdfVector($collegeUniversityTokens, $idfUniversity);

    $simCourse = cosineSimilarity($studentCourseVector, $collegeCourseVector);
    $simUniversity = cosineSimilarity($studentUniversityVector, $collegeUniversityVector);

    // Weighted sum similarity
    $similarity = $weights['course'] * $simCourse + $weights['university'] * $simUniversity;

    if ($similarity > 0.4) { // you can set a threshold here if needed
        $college['similarity'] = round($similarity, 4);
        $recommendations[] = $college;
    }
}

// Sort recommendations descending by similarity
usort($recommendations, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

// Return top 5 results
echo json_encode(array_slice($recommendations, 0, 5));
exit();
