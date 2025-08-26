<?php
require('../db_connection/db_connection.php');

if (!isset($_GET['college_id'])) {
    die("College ID is missing!");
}
$college_id = intval($_GET['college_id']);

// Fetch college name for the page title
$stmtCollege = $conn->prepare("SELECT name FROM colleges WHERE id = ?");
$stmtCollege->bind_param("i", $college_id);
$stmtCollege->execute();
$resCollege = $stmtCollege->get_result();
if ($resCollege->num_rows === 0) {
    die("College not found!");
}
$college = $resCollege->fetch_assoc();
$collegeName = htmlspecialchars($college['name']);
$stmtCollege->close();

// Fetch applications with prepared statement
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
    students.email
  FROM applications
  INNER JOIN students ON applications.student_id = students.id
  WHERE applications.college_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $college_id);
$stmt->execute();
$result = $stmt->get_result();
$applications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Applications for <?php echo $collegeName; ?></title>
    <link rel="stylesheet" href="admin_dashboard.css" />
    <style>
      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
      }
      th, td {
        padding: 12px 10px;
        border: 1px solid #ccc;
        text-align: left;
      }
      th {
        background-color: #40739e;
        color: white;
      }
      button {
        margin: 2px 4px;
        padding: 6px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        color: white;
        user-select: none;
      }
      button.approve {
        background-color: #44bd32;
      }
      button.reject {
        background-color: #e84118;
      }
      button.approve:hover {
        background-color: #4cd137;
      }
      button.reject:hover {
        background-color: #c23616;
      }
      a {
        color: #273c75;
        font-weight: 600;
        text-decoration: none;
      }
      a:hover {
        text-decoration: underline;
      }
    </style>
</head>
<body>
    <h1>Applications for <?php echo $collegeName; ?></h1>

    <table>
        <thead>
            <tr>
                <th>Application ID</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Entrance Rank</th>
                <th>Marksheet</th>
                <th>Background Faculty</th>
                <th>Applied At</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($applications) === 0): ?>
                <tr><td colspan="11" style="text-align:center;">No applications found for this college.</td></tr>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?php echo $app['application_id']; ?></td>
                        <td><?php echo htmlspecialchars($app['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($app['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($app['email']); ?></td>
                        <td><?php echo htmlspecialchars($app['course']); ?></td>
                        <td><?php echo htmlspecialchars($app['entrance_rank']); ?></td>
                        <td>
                            <?php if (!empty($app['marksheet_path'])): ?>
                                <a href="../uploads/<?php echo rawurlencode($app['marksheet_path']); ?>" target="_blank" rel="noopener noreferrer">View Marksheet</a>
                            <?php else: ?>
                                No file
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($app['background_faculty']); ?></td>
                        <td><?php echo htmlspecialchars($app['applied_at']); ?></td>
                        <td id="status-<?php echo $app['application_id']; ?>"><?php echo htmlspecialchars($app['status']); ?></td>
                        <td id="action-<?php echo $app['application_id']; ?>">
                            <?php if ($app['status'] === 'pending'): ?>
                                <button class="approve" onclick="confirmUpdateStatus(<?php echo $app['application_id']; ?>, 'approved')">Approve</button>
                                <button class="reject" onclick="confirmUpdateStatus(<?php echo $app['application_id']; ?>, 'rejected')">Reject</button>
                            <?php else: ?>
                                <em>completed</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

<script>
function confirmUpdateStatus(applicationId, status) {
    let actionText = status === 'approved' ? 'Approve' : 'Reject';
    if (confirm(`Are you sure you want to ${actionText} application #${applicationId}?`)) {
        updateStatus(applicationId, status);
    }
}

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
            document.getElementById("action-" + applicationId).innerHTML = "<em>completed</em>";
        }
    })
    .catch(err => {
        console.error("Failed to update status:", err);
        alert("Error updating application status. Please try again.");
    });
}
</script>

</body>
</html>
