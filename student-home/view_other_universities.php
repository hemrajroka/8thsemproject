<?php
session_start();
require('../db_connection/db_connection.php');

function getUniversityImage($universityName) {
    $images = [
        'Tribhuvan University' => 'images/Tu.png',
        'Pokhara University' => 'images/pokhara university.png',
        'Kathmandu University' => 'images/ku.png',
        'Purbanchal University' => 'images/purbanchal.png',
    ];
    return $images[$universityName] ?? 'images/default.png';
}

$selectedUniversity = $_GET['university'] ?? '';
$result = null;

if (!empty($selectedUniversity)) {
    $stmt = $conn->prepare("SELECT * FROM colleges WHERE university = ?");
    $stmt->bind_param("s", $selectedUniversity);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Other Universities</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f2f5;
      padding: 20px;
      margin-left: 200px; /* Adjust to accommodate sidebar */
    }

    h2 {
      color: #1c3a66;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .back-button {
      background-color: #1c3a66;
      color: #fff;
      padding: 8px 16px;
      text-decoration: none;
      border-radius: 5px;
    }

    .back-button:hover {
      background-color: #274c87;
    }

    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: 200px;
      height: 100%;
      background-color: #1c3a66;
      padding-top: 60px;
      display: flex;
      flex-direction: column;
    }
 .sidebar h2 {
      color: #f7b733;
      text-align: center;
      margin-bottom: 1.5rem;
    }

    .sidebar a {
      text-decoration: none;
      color: #f7b733;
      font-weight: 500;
      padding: 0.75rem 1rem;
      display: block;
      transition: background 0.2s ease;
    }

    .sidebar a:hover {
      background-color: #274c87;
    }

    .university-cards {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-top: 20px;
    }

    .university-card {
      width: 200px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      overflow: hidden;
      text-align: center;
      transition: transform 0.2s;
    }

    .university-card:hover {
      transform: scale(1.03);
    }

    .university-card img {
      width: 100%;
      height: 120px;
      object-fit: cover;
    }

    .university-card p {
      margin: 10px 0;
      font-weight: bold;
      color: #1c3a66;
    }

    .college-row {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-top: 20px;
    }

    .college-card {
      flex: 1 1 calc(33.33% - 20px);
      background: white;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      box-sizing: border-box;
      min-width: 280px;
    }

    .college-card h3 {
      margin-top: 0;
      color: #1c3a66;
    }

    .college-card p {
      margin: 6px 0;
    }

    .college-card .actions a {
      display: inline-block;
      margin-top: 10px;
      margin-right: 10px;
      text-decoration: none;
      padding: 6px 12px;
      border-radius: 5px;
      font-size: 14px;
    }

    .actions button {
      background-color: #f7b733;
      border: none;
      color: #1c3a66;
      padding: 0.5rem 1rem;
      font-weight: 700;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .actions button:hover {
      background-color: #d6a40d;
    }

    .view-btn {
      background-color: #1c3a66;
      color: #fff;
    }

    .view-btn:hover {
      background-color: #274c87;
    }

    .apply-btn {
      background-color: #f7b733;
      color: #1c3a66;
    }

    .apply-btn:hover {
      background-color: #e6a600;
    }

    @media (max-width: 768px) {
      body {
        margin-left: 0;
      }

      .sidebar {
        position: relative;
        width: 100%;
        height: auto;
        flex-direction: row;
        padding: 10px;
      }

      .sidebar a {
        flex: 1;
        text-align: center;
        padding: 10px;
      }

      .college-card {
        flex: 1 1 100%;
      }
    }
  </style>
</head>
<body>

  <div class="sidebar">
    <a href="/6thsem_project/student-home/student_home.html">Recommendations</a>
    <a href="/6thsem_project/student-home/applications.php">Application Status</a>
    <a href="/6thsem_project/student-home/view_all_colleges.php">All Colleges</a>
    <a href="/6thsem_project/student-home/view_other_universities.php">Universities</a>
    <a href="/6thsem_project/student-home/profile_update.php">Profile</a>
    <a href="/6thsem_project/login/login.html">Logout</a>
  </div>

  <!-- Top Bar -->
  <div class="top-bar">
    <h2>Browse Colleges by University</h2>
    <a class="back-button" href="/6thsem_project/student-home/view_all_colleges.php">⬅ Back to home</a>
  </div>

  <?php if (empty($selectedUniversity)) : ?>
    <!-- Show university selection cards -->
    <div class="university-cards">
      <?php
      $universities = [
        'Tribhuvan University' => 'logo/Tu.png',
        'Pokhara University' => 'logo/PU.png',
        'Kathmandu University' => 'logo/ku.png',
        'Purbanchal University' => 'logo/prubhanchal.png',
      ];

      foreach ($universities as $name => $imgPath) {
          echo '
          <div class="university-card">
            <a href="?university=' . urlencode($name) . '">
              <img src="' . $imgPath . '" alt="' . htmlspecialchars($name) . '">
              <p>' . htmlspecialchars($name) . '</p>
            </a>
          </div>';
      }
      ?>
    </div>
  <?php else: ?>
    <!-- Show colleges under selected university -->
    <h3>Colleges under: <?= htmlspecialchars($selectedUniversity) ?></h3>

    <?php
    if ($result && $result->num_rows > 0) {
        echo '<div class="college-row">';
        while ($college = $result->fetch_assoc()) {
            echo '<div class="college-card">';
            echo '<h3>' . htmlspecialchars($college['name']) . '</h3>';
            echo '<p><strong>Course:</strong> ' . htmlspecialchars($college['course']) . '</p>';
            echo '<p><strong>University:</strong> ' . htmlspecialchars($college['university']) . '</p>';
            echo '<p><strong>Location:</strong> ' . htmlspecialchars($college['location']) . '</p>';
            echo '<p><strong>Phone:</strong> ' . htmlspecialchars($college['phone_number']) . '</p>';
            echo '<p><strong>Scholarships:</strong> ' . htmlspecialchars($college['scholarships']) . '</p>';
            echo '<p><strong>Description:</strong> ' . htmlspecialchars($college['description']) . '</p>';
            echo '<div class="actions">';
            echo '<a class="view-btn" href="view_college_details.php?id=' . $college['id'] . '">View Details</a>';
            echo '<a class="apply-btn" href="apply_college.php?college_id=' . $college['id'] . '">Apply</a>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>No colleges found for this university.</p>';
    }
    ?>
  <?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>
