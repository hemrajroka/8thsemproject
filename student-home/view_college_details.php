<?php
require('../db_connection/db_connection.php');

if (!isset($_GET['id'])) {
  echo "No college selected.";
  exit();
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM colleges WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "College not found.";
  exit();
}

$college = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title><?php echo htmlspecialchars($college['name']); ?> - Details</title>
  <link rel="stylesheet" href="student_home.css" />
</head>
<body>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f6f8;
      color: #333;
      display: flex;
    }

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
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
      z-index: 100;
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
    .college-card {
      margin-left: 240px; /* Adjusted for sidebar width */
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      max-width: 800px;
      margin: 20px auto;
    }
    </style>
    <div class="sidebar">
      <a href="/8thsem_project/student-home/student_home.html">Recommendations</a>
    <a href="/8thsem_project/student-home/applications.php">Application Status</a>
    <a href="/8thsem_project/student-home/view_all_colleges.php">All colleges</a>
    <a href="/8thsem_project/student-home/view_other_universities.php">Universities</a>
    <a href="/8thsem_project/student-home/profile_update.php">Profile</a>
    
  </div>
  
  <div class="college-card">
    <h1><?php echo htmlspecialchars($college['name']); ?></h1>
    <p><strong>University:</strong> <?php echo $college['university']; ?></p>
    <p><strong>Course:</strong> <?php echo $college['course']; ?></p>
    <p><strong>Location:</strong> <?php echo $college['location']; ?></p>
    
    <p><strong>phone_number:</strong> <?php echo $college['phone_number']; ?></p>
    <p><strong>duration:</strong> <?php echo $college['duration']; ?></p>
   
    <p><strong>Description:</strong> <?php echo nl2br($college['description'] ?? 'No description provided.'); ?></p>

    
  </div>
</body>
</html>
