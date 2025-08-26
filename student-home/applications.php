<?php
session_start();
require('../db_connection/db_connection.php');

if (!isset($_SESSION['studentID'])) {
    header("Location: /6thsem_project/login/login.html");
    exit();
}

$studentID = $_SESSION['studentID'];

$sql = "SELECT a.id, c.name AS college_name, a.course, a.applied_at, a.status
        FROM applications a
        JOIN colleges c ON a.college_id = c.id
        WHERE a.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $studentID);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
  <title>Application Status</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f5f7fa;
      margin-left: 200px; /* space for sidebar */
      padding: 20px;
      box-sizing: border-box;
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


    h1 {
      margin-bottom: 20px;
      color: #1c3a66;
    }

    #application-list {
      display: grid;
      grid-template-columns: repeat(3, 1fr); /* 3 columns */
      gap: 20px;
      padding-top: 10px;
    }

    .college-card {
      background-color: #fff;
      border-radius: 12px;
      border: 1px solid #ddd;
      padding: 20px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
      transition: transform 0.2s;
      width: 100%;
      box-sizing: border-box;
    }

    .college-card:hover {
      transform: translateY(-5px);
    }

    .college-card h3 {
      margin-top: 0;
      font-size: 20px;
      color: #0073e6;
    }

    .college-card p {
      margin: 8px 0;
      font-size: 14px;
    }

    .college-card span {
      font-weight: bold;
    }

    .back-link {
      display: inline-block;
      margin-bottom: 20px;
      color: #0073e6;
      text-decoration: none;
      font-weight: bold;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      body {
        margin-left: 0;
        padding: 10px;
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

      #application-list {
        grid-template-columns: 1fr;
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

  <!-- Main Content -->
  <h1>Your Application Status</h1>
  <a class="back-link" href="student_home.html">← Back to Home</a>

  <div class="main-content">
    <div id="application-list">
      <?php
      if ($result->num_rows === 0) {
          echo "<p>No applications found.</p>";
      } else {
          while ($row = $result->fetch_assoc()) {
              $color = $row['status'] === 'approved' ? 'green' : ($row['status'] === 'rejected' ? 'red' : 'orange');
              $status = ucfirst($row['status']);
              echo "
              <div class='college-card'>
                <h3>{$row['college_name']}</h3>
                <p><strong>Course:</strong> {$row['course']}</p>
                <p><strong>Applied At:</strong> {$row['applied_at']}</p>
                <p><strong>Status:</strong> <span style='color: $color;'>$status</span></p>
              </div>
              ";
          }
      }
      ?>
    </div>
  </div>
</body>
</html>
