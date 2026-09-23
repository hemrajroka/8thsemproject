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

/*
|--------------------------------------------------------------------------
| KNOWN ABBREVIATIONS / ALIASES
|--------------------------------------------------------------------------
|
| Lets people type short forms ("ktm", "pkr", "tu") and have them
| understood as the full place/university name. Extend this map as
| needed — keys are lowercase, values are the nicely-cased full form.
|
*/

$aliasMap = [
    // Locations (Nepal)
    'ktm' => 'Kathmandu',
    'pkr' => 'Pokhara',
    'blt' => 'Biratnagar',
    'btl' => 'Butwal',
    'bkt' => 'Bhaktapur',
    'ldp' => 'Lalitpur',
    'dhrn' => 'Dharan',
    'npj' => 'Nepalgunj',
    'hty' => 'Hetauda',
    'brt' => 'Bharatpur',
    'itr' => 'Itahari',

    // Universities (Nepal)
    'tu' => 'Tribhuvan University',
    'ku' => 'Kathmandu University',
    'pu' => 'Pokhara University',
    'pku' => 'Purbanchal University',

    // Common course abbreviations — extend to match your DB's naming.
    'csit' => 'Computer Science',
    'cs' => 'Computer Science',
    'bit' => 'Information Technology',
    'bba' => 'Business Administration',
    'bbs' => 'Business Studies',
    'bsc' => 'Science',
];

function expandAliases(string $text, array $aliasMap): string
{
    return preg_replace_callback('/[a-zA-Z]+/', function ($match) use ($aliasMap) {
        $lower = strtolower($match[0]);
        return $aliasMap[$lower] ?? $match[0];
    }, $text);
}

$rawMessage = expandAliases($rawMessage, $aliasMap);
$message = strtolower($rawMessage);


/*
|--------------------------------------------------------------------------
| SMALL HELPERS
|--------------------------------------------------------------------------
*/

function pickReply(array $options): string
{
    return $options[array_rand($options)];
}

// Finds the "closest" known value (course/university/location) contained
// in, or containing, the user's free text. Lets people type naturally
// ("computer science", "at Tribhuvan University", "somewhere in Pokhara")
// instead of matching an exact step's expected format.
function bestKnownMatch(string $textLower, array $knownValuesLower): ?string
{
    $best = null;
    $bestLen = 0;

    foreach ($knownValuesLower as $v) {
        if ($v === '') {
            continue;
        }

        if (strpos($textLower, $v) !== false || strpos($v, $textLower) !== false) {
            $len = strlen($v);
            if ($len > $bestLen) {
                $bestLen = $len;
                $best = $v;
            }
        }
    }

    return $best;
}

// Catches casual/off-topic chat ("how are you", "who are you", "lol")
// so it doesn't get swallowed as the answer to whatever question was
// just asked. Returns a friendly reply, or null if this isn't small talk.
function smallTalkReply(string $textLower): ?string
{
    // Short, generic wellbeing answers — matched exactly so words like
    // "great" or "fine" don't misfire inside an unrelated sentence.
    $exactWellbeingAnswers = [
        'fine', 'good', 'great', "i'm fine", 'im fine', 'i am fine',
        "i'm good", 'im good', 'i am good', 'doing well', 'doing good',
        'doing great', 'not bad', 'pretty good', 'feeling good'
    ];

    if (in_array(trim($textLower), $exactWellbeingAnswers, true)) {
        return "Glad to hear it! 😊";
    }

    $groups = [
        'wellbeing' => ['how are you', 'how r u', 'how are u', "how's it going", 'hows it going', 'how do you do'],
        'identity' => ['who are you', 'what are you', "what's your name", 'whats your name', 'are you a robot', 'are you human', 'are you an ai', 'are you ai'],
        'capability' => ['what can you do', 'what do you do', 'can you help', 'help me'],
        'laughter' => ['haha', 'hehe', 'lmao', 'lol'],
    ];

    $replies = [
        'wellbeing' => "I am fine, thank you! 😊",
        'identity' => "I'm your college recommendation assistant — here to help you find a good-fit college. 😊",
        'capability' => 'I can look through our college list and recommend the best matches for your course, university, and location preferences. 😊',
        'laughter' => 'Glad that made you smile! 😄',
    ];

    foreach ($groups as $key => $phrases) {
        foreach ($phrases as $phrase) {
            if (strpos($textLower, $phrase) !== false) {
                return $replies[$key];
            }
        }
    }

    return null;
}

// Fallback for bestKnownMatch: catches small typos ("Katmandu" ->
// "Kathmandu") by comparing individual words with edit-distance
// tolerance, instead of requiring an exact substring.
function fuzzyKnownMatch(string $textLower, array $knownValuesLower): ?string
{
    $words = preg_split('/[^a-z0-9]+/i', $textLower, -1, PREG_SPLIT_NO_EMPTY);

    $best = null;
    $bestDistance = null;

    foreach ($knownValuesLower as $v) {
        if ($v === '' || strlen($v) < 3) {
            continue;
        }

        $vWords = preg_split('/[^a-z0-9]+/i', $v, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($words as $word) {
            if (strlen($word) < 3) {
                continue;
            }

            foreach ($vWords as $vWord) {
                if (strlen($vWord) < 3) {
                    continue;
                }

                $distance = levenshtein($word, $vWord);
                $allowedDistance = strlen($vWord) >= 6 ? 2 : 1;

                if ($distance <= $allowedDistance && ($bestDistance === null || $distance < $bestDistance)) {
                    $best = $v;
                    $bestDistance = $distance;
                }
            }
        }
    }

    return $best;
}

// Lets a student say things like "prioritize location" or "university
// matters most to me" to shift how much weight that factor carries in
// the hybrid score.
function detectPriority(string $textLower): ?string
{
    $map = [
        'course' => ['prioritize course', 'course matters most', 'course is more important', 'focus on course', 'course is most important'],
        'university' => ['prioritize university', 'university matters most', 'university is more important', 'focus on university', 'university is most important'],
        'location' => ['prioritize location', 'location matters most', 'location is more important', 'focus on location', 'location is most important'],
    ];

    foreach ($map as $slot => $phrases) {
        foreach ($phrases as $phrase) {
            if (strpos($textLower, $phrase) !== false) {
                return $slot;
            }
        }
    }

    return null;
}

function weightProfile(?string $priority): array
{
    $profiles = [
        'course' => ['course' => 0.40, 'university' => 0.30, 'location' => 0.20, 'cosine' => 0.10],
        'university' => ['course' => 0.25, 'university' => 0.45, 'location' => 0.20, 'cosine' => 0.10],
        'location' => ['course' => 0.25, 'university' => 0.25, 'location' => 0.40, 'cosine' => 0.10],
    ];

    return $profiles[$priority] ?? $profiles['course'];
}

// Builds an acronym from a college name's significant words, e.g.
// "Asian College of Higher Studies" -> "achs" (skipping small
// connector words like "of"/"the"/"and").
function collegeAcronym(string $name): string
{
    $stopWords = ['of', 'the', 'and', 'for', 'in', 'at', '&'];
    $words = preg_split('/[^a-zA-Z0-9]+/', $name, -1, PREG_SPLIT_NO_EMPTY);

    $initials = '';
    foreach ($words as $w) {
        if (in_array(strtolower($w), $stopWords, true)) {
            continue;
        }
        $initials .= strtolower($w[0]);
    }

    return $initials;
}

// Finds a college from a saved candidate list by name, tolerating
// partial phrasing ("tell me more about Xavier") and small typos.
// Stricter than findCandidateByName — substring/acronym match only, no
// fuzzy fallback. Used when the whole message might just be a college
// name typed on its own, so we don't want loose typo-tolerance
// misfiring on unrelated short messages.
function findCandidateByNameStrict(array $candidates, string $needle): ?array
{
    $needle = strtolower(trim($needle));
    if (strlen($needle) < 3) {
        return null;
    }

    foreach ($candidates as $c) {
        $name = strtolower(trim($c['name'] ?? ''));
        if ($name === '') {
            continue;
        }

        if (strpos($name, $needle) !== false || strpos($needle, $name) !== false) {
            return $c;
        }

        if (collegeAcronym($c['name'] ?? '') === $needle) {
            return $c;
        }
    }

    return null;
}

function findCandidateByName(array $candidates, string $needle): ?array
{
    $needle = strtolower(trim($needle));
    if ($needle === '') {
        return null;
    }

    foreach ($candidates as $c) {
        $name = strtolower(trim($c['name'] ?? ''));
        if ($name === '') {
            continue;
        }

        if (strpos($name, $needle) !== false || strpos($needle, $name) !== false) {
            return $c;
        }

        if (collegeAcronym($c['name'] ?? '') === $needle) {
            return $c;
        }
    }

    $best = null;
    $bestDistance = null;

    foreach ($candidates as $c) {
        $name = strtolower(trim($c['name'] ?? ''));
        if ($name === '') {
            continue;
        }

        $distance = levenshtein($needle, $name);
        if ($bestDistance === null || $distance < $bestDistance) {
            $bestDistance = $distance;
            $best = $c;
        }
    }

    $tolerance = max(3, (int) (strlen($needle) * 0.4));

    return ($best !== null && $bestDistance <= $tolerance) ? $best : null;
}

function collegeDetailReply(array $c): string
{
    $scholarships = trim((string) ($c['scholarships'] ?? ''));

    return
        '<strong>' . htmlspecialchars($c['name'] ?? '') . '</strong><br>' .
        'Course: ' . htmlspecialchars($c['course'] ?: '-') . '<br>' .
        'University: ' . htmlspecialchars($c['university'] ?: '-') . '<br>' .
        'Location: ' . htmlspecialchars($c['location'] ?: '-') . '<br>' .
        'Duration: ' . htmlspecialchars($c['duration'] ?: '-') . '<br>' .
        'Scholarships: ' . htmlspecialchars($scholarships !== '' ? $scholarships : 'None listed') . '<br>' .
        'Phone: ' . htmlspecialchars($c['phone_number'] ?: '-') .
        (!empty($c['description']) ? '<br><br>' . htmlspecialchars($c['description']) : '');
}

function compareCollegesReply(array $a, array $b): string
{
    $fields = [
        'course' => 'Course',
        'university' => 'University',
        'location' => 'Location',
        'duration' => 'Duration',
        'scholarships' => 'Scholarships',
        'phone_number' => 'Phone',
    ];

    $html = '<strong>' . htmlspecialchars($a['name'] ?? '') . '</strong> vs ' .
        '<strong>' . htmlspecialchars($b['name'] ?? '') . '</strong><br><br>' .
        '<table border="1" cellpadding="6" cellspacing="0">' .
        '<tr><th></th><th>' . htmlspecialchars($a['name'] ?? '') . '</th><th>' . htmlspecialchars($b['name'] ?? '') . '</th></tr>';

    foreach ($fields as $key => $label) {
        $va = trim((string) ($a[$key] ?? ''));
        $vb = trim((string) ($b[$key] ?? ''));

        $html .= '<tr><td><strong>' . htmlspecialchars($label) . '</strong></td>' .
            '<td>' . htmlspecialchars($va !== '' ? $va : '-') . '</td>' .
            '<td>' . htmlspecialchars($vb !== '' ? $vb : '-') . '</td></tr>';
    }

    return $html . '</table>';
}

function isScholarshipListed(array $c): bool
{
    $s = strtolower(trim((string) ($c['scholarships'] ?? '')));
    return $s !== '' && !in_array($s, ['none', 'no', 'n/a', 'na', 'not available'], true);
}

// Caches the distinct course/university/location values for a few
// minutes so we're not hitting the DB on every single chat message.
function getKnownValues(mysqli $conn): array
{
    $cacheTtl = 300;
    $now = time();

    if (
        isset($_SESSION['known_lists'], $_SESSION['known_lists_at']) &&
        ($now - $_SESSION['known_lists_at']) < $cacheTtl
    ) {
        return $_SESSION['known_lists'];
    }

    $knownCourses = [];
    $knownUniversities = [];
    $knownLocations = [];

    $lookup = $conn->query("SELECT DISTINCT course, university, location FROM colleges");

    if ($lookup) {
        while ($row = $lookup->fetch_assoc()) {
            if (!empty($row['course'])) {
                $knownCourses[strtolower(trim($row['course']))] = true;
            }
            if (!empty($row['university'])) {
                $knownUniversities[strtolower(trim($row['university']))] = true;
            }
            if (!empty($row['location'])) {
                $knownLocations[strtolower(trim($row['location']))] = true;
            }
        }
    }

    $lists = [
        'courses' => array_keys($knownCourses),
        'universities' => array_keys($knownUniversities),
        'locations' => array_keys($knownLocations),
    ];

    $_SESSION['known_lists'] = $lists;
    $_SESSION['known_lists_at'] = $now;

    return $lists;
}

function isSkipPhrase(string $textLower): bool
{
    $skipPhrases = [
        'any', 'anything', 'anyone', 'anywhere',
        'no preference', "doesn't matter", 'does not matter',
        'whatever', 'skip', "don't mind", 'dont mind',
        'not sure', 'no idea', "i don't know", 'i dont know',
        "i don't care", 'i dont care', 'not important'
    ];

    foreach ($skipPhrases as $phrase) {
        if ($textLower === $phrase || strpos($textLower, $phrase) !== false) {
            return true;
        }
    }

    return false;
}


/*
|--------------------------------------------------------------------------
| RESTART
|--------------------------------------------------------------------------
*/

$restartWords = [
    'restart', 'start again', 'start over',
    'new recommendation', 'recommend again', 'reset'
];

if (in_array($message, $restartWords)) {

    unset(
        $_SESSION['chat_step'],
        $_SESSION['chat_data'],
        $_SESSION['chat_asking'],
        $_SESSION['last_candidates'],
        $_SESSION['weight_priority']
    );

    $_SESSION['chat_step'] = 'collecting';
    $_SESSION['chat_data'] = ['course' => null, 'university' => null, 'location' => null];
    $_SESSION['chat_asking'] = 'multiple';

    echo json_encode([
        'success' => true,
        'reply' =>
            'Sure ' . htmlspecialchars($studentName) .
            '! 👋 Let\'s find colleges for you.' .
            '<br><br>Tell me a bit about what you\'re looking for — ' .
            'for example the course, university, or location you have in mind.'
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| GREETING (can happen any time, doesn't erase progress)
|--------------------------------------------------------------------------
*/

$greetings = ['hi', 'hello', 'hey', 'hii', 'hiii', 'namaste', 'good morning', 'good evening'];

$isGreeting = in_array($message, $greetings);

if ($isGreeting && (!isset($_SESSION['chat_step']) || $_SESSION['chat_step'] === 'completed')) {

    $_SESSION['chat_step'] = 'collecting';
    $_SESSION['chat_data'] = ['course' => null, 'university' => null, 'location' => null];
    $_SESSION['chat_asking'] = 'multiple';

    echo json_encode([
        'success' => true,
        'reply' =>
            'Hello ' . htmlspecialchars($studentName) . '! 👋 How are you?<br><br>' .
            'I can help you find a good-fit college. Just tell me what you\'re after — ' .
            'e.g. "I want to study Computer Science, preferably in Pokhara" — ' .
            'and I\'ll ask about anything you leave out.'
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| INITIALISE ON FIRST MESSAGE
|--------------------------------------------------------------------------
*/

$firstMessage = false;

if (!isset($_SESSION['chat_step'])) {
    $_SESSION['chat_step'] = 'collecting';
    $_SESSION['chat_data'] = ['course' => null, 'university' => null, 'location' => null];
    $_SESSION['chat_asking'] = 'multiple';
    $firstMessage = true;
}


/*
|--------------------------------------------------------------------------
| MID-CONVERSATION GREETING (don't let "hi" get captured as an answer)
|--------------------------------------------------------------------------
*/

if ($isGreeting && $_SESSION['chat_step'] === 'collecting' && !$firstMessage) {

    $missingNow = array_keys(array_filter(
        $_SESSION['chat_data'],
        fn($v) => $v === null
    ));

    $reprompt = count($missingNow) > 0
        ? ' Just to pick up where we left off — ' . askForSlots($missingNow)
        : '';

    echo json_encode([
        'success' => true,
        'reply' => 'Hey there! 👋' . $reprompt
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| QUESTION PHRASING HELPERS
|--------------------------------------------------------------------------
*/

function askForSlots(array $missingSlots): string
{
    $questions = [
        'course' => [
            'What course are you interested in?',
            'Which course would you like to study?',
            'Any particular course in mind?'
        ],
        'university' => [
            'Which university do you prefer?',
            'Is there a university you\'re hoping for?',
            'Do you have a university in mind?'
        ],
        'location' => [
            'Which location do you prefer?',
            'Any preferred location?',
            'Where would you like the college to be?'
        ],
    ];

    if (count($missingSlots) === 1) {
        $slot = $missingSlots[0];
        return pickReply($questions[$slot]) . ' (You can also just say "any" if it doesn\'t matter.)';
    }

    $labels = array_map(fn($s) => ucfirst($s), $missingSlots);
    $last = array_pop($labels);
    $list = count($labels) > 0 ? implode(', ', $labels) . ' and ' . $last : $last;

    return 'Could you tell me your preferred ' . strtolower($list) .
        '? Feel free to mention them all in one message, or say "any" for ones that don\'t matter to you.';
}


/*
|--------------------------------------------------------------------------
| COLLECTING STEP - free-form slot filling
|--------------------------------------------------------------------------
*/

if ($_SESSION['chat_step'] === 'collecting') {

    $known = getKnownValues($conn);
    $knownCourses = $known['courses'];
    $knownUniversities = $known['universities'];
    $knownLocations = $known['locations'];

    $data = $_SESSION['chat_data'];
    $askingBefore = $_SESSION['chat_asking'] ?? 'multiple';

    // Typing a bare "any" (or equivalent) means "no filters at all" —
    // show every college in the database, regardless of anything
    // collected so far.
    $showAllPhrases = [
        'any', 'anything', 'any college', 'any colleges',
        'all colleges', 'all the colleges', 'show all',
        'show everything', 'everything', 'all'
    ];

    $showAllRequested = in_array(trim($message), $showAllPhrases, true);

    $priority = detectPriority($message);

    if ($priority !== null) {
        $_SESSION['weight_priority'] = $priority;

        $missingNow = array_keys(array_filter($data, fn($v) => $v === null));
        $followUp = count($missingNow) > 0 ? ' ' . askForSlots($missingNow) : '';

        echo json_encode([
            'success' => true,
            'reply' => 'Got it — I\'ll weigh <strong>' . htmlspecialchars($priority) .
                '</strong> more heavily when ranking colleges for you.' . $followUp
        ]);
        exit();
    }

    if ($showAllRequested) {
        $data = ['course' => '', 'university' => '', 'location' => ''];
        $_SESSION['chat_data'] = $data;
        $_SESSION['chat_asking'] = 'multiple';
        $filledSomething = true;
        $isSkip = true;
    } else {

    if ($smallTalk = smallTalkReply($message)) {
        $missingNow = array_keys(array_filter($data, fn($v) => $v === null));
        $followUp = count($missingNow) > 0 ? ' ' . askForSlots($missingNow) : '';

        echo json_encode([
            'success' => true,
            'reply' => $smallTalk . $followUp
        ]);
        exit();
    }

    $filledSomething = false;

    if ($isSkip = isSkipPhrase($message)) {
        // "Any"/"doesn't matter" only resolves a single question we just asked.
        if ($askingBefore !== 'multiple' && $data[$askingBefore] === null) {
            $data[$askingBefore] = ''; // '' = no preference
            $filledSomething = true;
        }
    }

    if (!$filledSomething) {
        // Try to pull out course / university / location from free text —
        // exact known-value match first, then a typo-tolerant fuzzy pass.
        if ($data['course'] === null) {
            $m = bestKnownMatch($message, $knownCourses) ?? fuzzyKnownMatch($message, $knownCourses);
            if ($m !== null) {
                $data['course'] = $m;
                $filledSomething = true;
            }
        }

        if ($data['university'] === null) {
            $m = bestKnownMatch($message, $knownUniversities) ?? fuzzyKnownMatch($message, $knownUniversities);
            if ($m !== null) {
                $data['university'] = $m;
                $filledSomething = true;
            }
        }

        if ($data['location'] === null) {
            $m = bestKnownMatch($message, $knownLocations) ?? fuzzyKnownMatch($message, $knownLocations);
            if ($m !== null) {
                $data['location'] = $m;
                $filledSomething = true;
            }
        }

        // Nothing recognised from known values, but we asked about exactly
        // one thing last time — take the reply as-is, same as answering a
        // direct question (keeps it working for courses not yet in the DB).
        if (!$filledSomething && $askingBefore !== 'multiple' && $data[$askingBefore] === null && !$isSkip) {
            $data[$askingBefore] = $rawMessage;
            $filledSomething = true;
        }
    }

    } // end else (not a "show all" shortcut)


    $_SESSION['chat_data'] = $data;

    $missing = array_keys(array_filter($data, fn($v) => $v === null));

    // Nothing understood at all and we still have multiple open questions.
    if (!$filledSomething && count($missing) > 1) {
        $intro = $firstMessage
            ? 'Hello ' . htmlspecialchars($studentName) . '! 👋<br><br>'
            : '';

        echo json_encode([
            'success' => true,
            'reply' => $intro .
                'I can help you find suitable colleges. ' . askForSlots($missing)
        ]);
        exit();
    }

    if (count($missing) > 0) {
        $_SESSION['chat_asking'] = count($missing) === 1 ? $missing[0] : 'multiple';

        $ack = $filledSomething ? pickReply(['Got it! 👍', 'Great! 👍', 'Good choice! 👍', 'Noted! 👍']) . ' ' : '';

        echo json_encode([
            'success' => true,
            'reply' => $ack . askForSlots($missing)
        ]);
        exit();
    }


    /*
    |--------------------------------------------------------------------------
    | ALL SLOTS FILLED - MATCH COLLEGES
    |--------------------------------------------------------------------------
    */

    $course = $data['course'] ?? '';
    $university = $data['university'] ?? '';
    $location = $data['location'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | SAVE PREFERENCES
    |--------------------------------------------------------------------------
    */

    $deleteStmt = $conn->prepare("DELETE FROM student_preferences WHERE student_id = ?");
    $deleteStmt->bind_param("i", $studentId);
    $deleteStmt->execute();
    $deleteStmt->close();

    $insertStmt = $conn->prepare("
        INSERT INTO student_preferences
        (student_id, preferred_course, preferred_university, preferred_location)
        VALUES (?, ?, ?, ?)
    ");
    $insertStmt->bind_param("isss", $studentId, $course, $university, $location);
    $insertStmt->execute();
    $insertStmt->close();


    /*
    |--------------------------------------------------------------------------
    | GET COLLEGES
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT id, name, course, university, location, phone_number,
               scholarships, duration, description
        FROM colleges
    ";

    $result = $conn->query($sql);
    $allRows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $totalColleges = count($allRows);

    // If the student has no real preference on any field, there's nothing
    // left to filter or rank by — just return every college we have,
    // instead of an arbitrary top 5.
    $noPreferenceAtAll = ($course === '' && $university === '' && $location === '');

    $weights = weightProfile($_SESSION['weight_priority'] ?? null);

    /*
    |--------------------------------------------------------------------------
    | PASS 1 — DOCUMENT FREQUENCY (for TF-IDF)
    |--------------------------------------------------------------------------
    |
    | How many colleges each word appears in, so common words (e.g.
    | "bachelor", "college") count for less than distinctive ones.
    |
    */

    $documentFrequency = [];
    $collegeWordSets = [];

    foreach ($allRows as $i => $row) {
        $collegeText = strtolower(
            ($row['name'] ?? '') . ' ' .
            ($row['course'] ?? '') . ' ' .
            ($row['university'] ?? '') . ' ' .
            ($row['location'] ?? '') . ' ' .
            ($row['description'] ?? '') . ' ' .
            ($row['scholarships'] ?? '')
        );

        $words = preg_split('/[^a-z0-9]+/i', $collegeText, -1, PREG_SPLIT_NO_EMPTY);
        $collegeWordSets[$i] = $words;

        foreach (array_unique($words) as $w) {
            $documentFrequency[$w] = ($documentFrequency[$w] ?? 0) + 1;
        }
    }

    function idfWeight(string $word, array $documentFrequency, int $totalDocs): float
    {
        $df = $documentFrequency[$word] ?? 0;
        return log(($totalDocs + 1) / ($df + 1)) + 1;
    }

    /*
    |--------------------------------------------------------------------------
    | PASS 2 — SCORE EACH COLLEGE
    |--------------------------------------------------------------------------
    */

    $colleges = [];

    $preferredCourse = strtolower(trim($course));
    $preferredUniversity = strtolower(trim($university));
    $preferredLocation = strtolower(trim($location));

    $preferenceText = trim($preferredCourse . ' ' . $preferredUniversity . ' ' . $preferredLocation);
    $queryWords = preg_split('/[^a-z0-9]+/i', $preferenceText, -1, PREG_SPLIT_NO_EMPTY);

    // TF-IDF weighted query vector (weighted once, reused for every college).
    $queryTermFreq = [];
    foreach ($queryWords as $w) {
        $queryTermFreq[$w] = ($queryTermFreq[$w] ?? 0) + 1;
    }
    $queryVector = [];
    foreach ($queryTermFreq as $w => $tf) {
        $queryVector[$w] = $tf * idfWeight($w, $documentFrequency, $totalColleges);
    }

    foreach ($allRows as $i => $row) {

        $collegeCourse = strtolower(trim($row['course'] ?? ''));
        $collegeUniversity = strtolower(trim($row['university'] ?? ''));
        $collegeLocation = strtolower(trim($row['location'] ?? ''));

        // COURSE MATCH ('' means the student had no preference)
        $courseMatch = 0;
        if ($preferredCourse === '') {
            $courseMatch = 1;
        } elseif (
            strpos($collegeCourse, $preferredCourse) !== false ||
            strpos($preferredCourse, $collegeCourse) !== false
        ) {
            $courseMatch = 1;
        }

        // UNIVERSITY MATCH
        $universityMatch = 0;
        if ($preferredUniversity === '') {
            $universityMatch = 1;
        } elseif (
            strpos($collegeUniversity, $preferredUniversity) !== false ||
            strpos($preferredUniversity, $collegeUniversity) !== false
        ) {
            $universityMatch = 1;
        }

        // LOCATION MATCH
        $locationMatch = 0;
        if ($preferredLocation === '') {
            $locationMatch = 1;
        } elseif (
            strpos($collegeLocation, $preferredLocation) !== false ||
            strpos($preferredLocation, $collegeLocation) !== false
        ) {
            $locationMatch = 1;
        }

        // TF-IDF weighted college vector, using the same IDF table as the query.
        $collegeTermFreq = [];
        foreach ($collegeWordSets[$i] as $w) {
            $collegeTermFreq[$w] = ($collegeTermFreq[$w] ?? 0) + 1;
        }
        $collegeVector = [];
        foreach ($collegeTermFreq as $w => $tf) {
            $collegeVector[$w] = $tf * idfWeight($w, $documentFrequency, $totalColleges);
        }

        $allWords = array_unique(array_merge(array_keys($queryVector), array_keys($collegeVector)));

        $dotProduct = 0;
        $queryMagnitude = 0;
        $collegeMagnitude = 0;

        foreach ($allWords as $w) {
            $q = $queryVector[$w] ?? 0;
            $c = $collegeVector[$w] ?? 0;

            $dotProduct += $q * $c;
            $queryMagnitude += $q * $q;
            $collegeMagnitude += $c * $c;
        }

        $cosineSimilarity = ($queryMagnitude > 0 && $collegeMagnitude > 0)
            ? $dotProduct / (sqrt($queryMagnitude) * sqrt($collegeMagnitude))
            : 0;

        // HYBRID ALGORITHM — weights shift if the student set a priority.
        $hybridScore =
            ($courseMatch * $weights['course']) +
            ($universityMatch * $weights['university']) +
            ($locationMatch * $weights['location']) +
            ($cosineSimilarity * $weights['cosine']);

        $row['score'] = round($hybridScore * 100, 2);

        // Only colleges with 50% or higher are considered suitable.
        if ($row['score'] >= 50) {
            $colleges[] = $row;
        }
    }

    // Sort everyone by their hybrid score (ties broken alphabetically by name).
    usort($colleges, function ($a, $b) {
        if ($a['score'] == $b['score']) {
            return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
        }
        return ($a['score'] < $b['score']) ? 1 : -1;
    });

    // Save the full ranked pool (not just the top 5) so follow-up questions
    // like "tell me more about X" or "compare X and Y" can reference it.
    $_SESSION['last_candidates'] = $colleges;

    // With no preference at all, everyone ties on the same hybrid score,
    // so there's no meaningful "top 5" — return the full list instead.
    $recommendations = $noPreferenceAtAll ? $colleges : array_slice($colleges, 0, 5);

    $_SESSION['chat_step'] = 'completed';


    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    $courseLabel = $course === '' ? 'Any' : htmlspecialchars($course);
    $universityLabel = $university === '' ? 'Any' : htmlspecialchars($university);
    $locationLabel = $location === '' ? 'Any' : htmlspecialchars($location);

    if (count($recommendations) > 0) {

        if ($noPreferenceAtAll) {
            $reply =
                'Sure, ' . htmlspecialchars($studentName) . '! 🎓' .
                '<br><br>' .
                'Since you don\'t have a specific course, university, or location in mind, ' .
                'here are <strong>all</strong> the colleges we have:';
        } else {
            $reply =
                'Thank you, ' . htmlspecialchars($studentName) . '! 🎓' .
                '<br><br>' .
                'I found colleges with a match score of <strong>50% or higher</strong>.' .
                '<br><br>' .
                '<strong>Course:</strong> ' . $courseLabel . '<br>' .
                '<strong>University:</strong> ' . $universityLabel . '<br>' .
                '<strong>Location:</strong> ' . $locationLabel .
                '<br><br>' .
                'Here are the suitable colleges:';
        }

        $reply .= '<br><br><em>Just type a college\'s name to see its details, or try ' .
            '"compare A and B", "only show ones with scholarships", or "show more colleges".</em>';

    } elseif ($noPreferenceAtAll) {

        $reply =
            'Hmm, ' . htmlspecialchars($studentName) .
            ', it looks like there are no colleges in the system yet.' .
            '<br><br>' .
            'You can type <strong>restart</strong> to try again later.';

    } else {

        $reply =
            'Sorry ' . htmlspecialchars($studentName) .
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

    $thanksWords = ['thanks', 'thank you', 'thankyou', 'ok', 'okay', 'great', 'nice'];

    if (in_array($message, $thanksWords)) {
        echo json_encode([
            'success' => true,
            'reply' => 'You\'re welcome! 😊 Type <strong>restart</strong> anytime to search again.'
        ]);
        exit();
    }

    if ($smallTalk = smallTalkReply($message)) {
        echo json_encode([
            'success' => true,
            'reply' => $smallTalk . ' Type <strong>restart</strong> anytime to search for colleges again.'
        ]);
        exit();
    }

    $candidates = $_SESSION['last_candidates'] ?? [];

    // "compare X and Y"
    if (preg_match('/compare\s+(.+?)\s+(?:and|vs\.?|versus)\s+(.+)/i', $rawMessage, $m)) {
        $a = findCandidateByName($candidates, $m[1]);
        $b = findCandidateByName($candidates, $m[2]);

        if ($a && $b) {
            echo json_encode(['success' => true, 'reply' => compareCollegesReply($a, $b)]);
        } else {
            echo json_encode([
                'success' => true,
                'reply' => "I couldn't find one or both of those colleges in your last results. " .
                    'Double-check the spelling, or type <strong>restart</strong> to search again.'
            ]);
        }
        exit();
    }

    // "tell me more about X" / "details about X" / "info on X"
    if (preg_match('/(?:tell me more about|more about|details? (?:on|about)|info(?:rmation)? (?:on|about))\s+(.+)/i', $rawMessage, $m)) {
        $target = findCandidateByName($candidates, $m[1]);

        if ($target) {
            echo json_encode(['success' => true, 'reply' => collegeDetailReply($target)]);
        } else {
            echo json_encode([
                'success' => true,
                'reply' => "I couldn't find that college in your last results. " .
                    'Double-check the spelling, or type <strong>restart</strong> to search again.'
            ]);
        }
        exit();
    }

    // "only show ones with scholarships" / anything mentioning scholarships
    if (strpos($message, 'scholarship') !== false) {
        $withScholarship = array_values(array_filter($candidates, 'isScholarshipListed'));

        if (count($withScholarship) > 0) {
            echo json_encode([
                'success' => true,
                'reply' => 'Here are the colleges from your last results that list scholarships:',
                'recommendations' => $withScholarship
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'reply' => 'None of the colleges in your last results have scholarships listed. ' .
                    'Type <strong>restart</strong> to search with different preferences.'
            ]);
        }
        exit();
    }

    // "show more" / "show all" / "see more"
    $showMorePhrases = ['show more', 'see more', 'show all', 'see all', 'more colleges', 'more options'];
    if (in_array(trim($message), $showMorePhrases, true) || strpos($message, 'show more') !== false) {
        echo json_encode([
            'success' => true,
            'reply' => count($candidates) > 0
                ? 'Here are all the matching colleges from your last search:'
                : "I don't have any saved results right now. Type <strong>restart</strong> to search again.",
            'recommendations' => $candidates
        ]);
        exit();
    }

    // Just typing a college's name on its own shows its details —
    // no need to say "tell me more about" first.
    $bareNameMatch = findCandidateByNameStrict($candidates, $rawMessage);
    if ($bareNameMatch !== null) {
        echo json_encode(['success' => true, 'reply' => collegeDetailReply($bareNameMatch)]);
        exit();
    }

    echo json_encode([
        'success' => true,
        'reply' =>
            'Your recommendation has already been generated. 😊' .
            '<br><br>' .
            'Just type a college\'s name to see its details, or try "compare A and B", ' .
            '"only show ones with scholarships", "show more colleges" — ' .
            'or type <strong>restart</strong> to search again.'
    ]);

    exit();
}


/*
|--------------------------------------------------------------------------
| DEFAULT (shouldn't normally be reached)
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