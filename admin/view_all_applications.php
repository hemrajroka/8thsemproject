<?php
require('../db_connection/db_connection.php');

$sql = "
  SELECT 
    applications.id AS application_id,
    applications.student_id,
    applications.course,
    applications.applied_at,
    applications.marksheet_path,
    applications.entrance_rank,
    applications.background_faculty,
    applications.status,
    students.fullname,
    students.email,
    colleges.name AS college_name
  FROM applications
  INNER JOIN students ON applications.student_id = students.id
  INNER JOIN colleges ON applications.college_id = colleges.id
  WHERE applications.status = 'pending'
  ORDER BY applications.applied_at DESC
";

$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$applications = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Pending Applications</title>
    <style>
      /* (Add your CSS styles here or link to your CSS file) */
      table {
        width: 100%; border-collapse: collapse;
      }
      th, td {
        border: 1px solid #ccc; padding: 8px; text-align: left;
      }
      th {
        background-color: #40739e; color: white;
      }
      button {
        margin: 2px;
        padding: 6px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.9rem;
        color: white;
      }
      button.approve { background-color: #44bd32; }
      button.reject { background-color: #e84118; }
    </style>
</head>
<body>
  <h1>All Pending Applications</h1>
  <table>
    <thead>
      <tr>
        <th>Application ID</th>
        <th>Student Name</th>
        <th>Email</th>
        <th>College</th>
        <th>Course</th>
        <th>Entrance Rank</th>
        <th>Marksheet</th>
        <th>Background Faculty</th>
        <th>Applied At</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($applications as $app): ?>
      <tr>
        <td><?= $app['application_id'] ?></td>
        <td><?= htmlspecialchars($app['fullname']) ?></td>
        <td><?= htmlspecialchars($app['email']) ?></td>
        <td><?= htmlspecialchars($app['college_name']) ?></td>
        <td><?= htmlspecialchars($app['course']) ?></td>
        <td><?= htmlspecialchars($app['entrance_rank']) ?></td>
        <td>
          <?php if ($app['marksheet_path']): ?>
            <a href="../uploads/<?= htmlspecialchars($app['marksheet_path']) ?>" target="_blank">View</a>
          <?php else: ?>
            No file
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($app['background_faculty']) ?></td>
        <td><?= htmlspecialchars($app['applied_at']) ?></td>
        <td id="status-<?= $app['application_id'] ?>"><?= htmlspecialchars($app['status']) ?></td>
        <td id="action-<?= $app['application_id'] ?>">
          <button class="approve" onclick="updateStatus(<?= $app['application_id'] ?>, 'approved')">Approve</button>
          <button class="reject" onclick="updateStatus(<?= $app['application_id'] ?>, 'rejected')">Reject</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <script>
    function updateStatus(applicationId, status) {
      const formData = new FormData();
      formData.append("application_id", applicationId);
      formData.append("status", status);

      fetch("update_application_status.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        if (data.success) {
          document.getElementById("status-" + applicationId).innerText = status;
          document.getElementById("action-" + applicationId).innerHTML = "Completed";
        }
      })
      .catch(err => console.error("Failed to update status:", err));
    }
  </script>
</body>
</html>
