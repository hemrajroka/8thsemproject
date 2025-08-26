<?php
session_start();
require_once '../db_connection/db_connection.php';

$id = 0;
$name = $university = $location = $phone_number = $fee = $duration = $scholarships = $description = "";
$courses = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM colleges WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $college = $result->fetch_assoc();
        $name = $college['name'];
        $university = $college['university'];
        $location = $college['location'];
        $phone_number = $college['phone_number'];
        $courses = explode(',', $college['course']);
        
        $duration = $college['duration'];
        $scholarships = $college['scholarships'];
        $description = $college['description'];
    } else {
        $_SESSION['message'] = "College not found.";
        header("Location: admin_dashboard.php");
        exit;
    }
}

// Handle AJAX form submission (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = trim($_POST['name'] ?? '');
    $university = $_POST['university'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $courses = isset($_POST['course']) ? $_POST['course'] : [];
    
    $duration = $_POST['duration'] ?? '';
    $scholarships = trim($_POST['scholarships'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validation
    if (empty($name)) $errors[] = "College name is required.";
    if (empty($university)) $errors[] = "University is required.";
    if (empty($location)) $errors[] = "Location is required.";
    if (empty($phone_number)) $errors[] = "Phone number is required.";
    if (empty($courses)) $errors[] = "At least one course must be selected.";
    if (empty($duration)) $errors[] = "Duration is required.";

    if (count($errors) === 0) {
        $courseStr = implode(',', $courses);

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE colleges SET name=?, course=?, university=?, location=?, phone_number=?, scholarships=?, duration=?, description=? WHERE id=?");
            $stmt->bind_param("ssssssisi", $name, $courseStr,  $university, $location, $phone_number, $scholarships, $duration, $description, $id);
            $success = $stmt->execute();
            $message = $success ? "College updated successfully." : "Failed to update college.";
        } else {
            $stmt = $conn->prepare("INSERT INTO colleges (name, course, university, location, phone_number, scholarships, duration, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $name, $courseStr,  $university, $location, $phone_number, $scholarships, $duration, $description);
            $success = $stmt->execute();
            $message = $success ? "College added successfully." : "Failed to add college.";
        }

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => $message,
                'redirect' => 'admin_dashboard.php'
            ]);
            exit;
        } else {
            $errors[] = $message;
        }
    }

    echo json_encode([
        'success' => false,
        'errors' => $errors
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?php echo $id ? 'Edit College' : 'Add College'; ?></title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 700px; margin: 20px auto; }
    form { display: flex; flex-direction: column; gap: 12px; }
    label { font-weight: bold; }
    input[type=text], input[type=tel], input[type=number], select, textarea {
      width: 100%; padding: 8px; box-sizing: border-box;
    }
    .checkbox-group label { font-weight: normal; margin-right: 12px; }
    button { padding: 10px 15px; font-size: 1rem; cursor: pointer; }
    .errors { background: #ffdddd; border: 1px solid #ff5c5c; padding: 10px; margin-bottom: 15px; }
    #message { margin-bottom: 15px; }
  </style>
</head>
<body>

  <h1><?php echo $id ? 'Edit College' : 'Add College'; ?></h1>

  <div id="message"></div>

  <form id="collegeForm" method="post" action="college.register.php<?php echo $id ? '?id=' . $id : ''; ?>">
    <input type="hidden" name="id" value="<?php echo $id; ?>" />

    <label>College Name</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required />

    <label>University</label>
    <select name="university" required>
      <option value="" disabled <?php if (!$university) echo "selected"; ?>>Select University</option>
      <?php
      $unis = ['Tribhuvan University', 'Pokhara University', 'Kathmandu University', 'Purbanchal University'];
      foreach ($unis as $uni) {
          $selected = ($university === $uni) ? 'selected' : '';
          echo "<option value=\"$uni\" $selected>$uni</option>";
      }
      ?>
    </select>

    <label>Location</label>
    <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>" required />

    <label>Phone Number</label>
    <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>" required />

    <label>Courses Offered</label>
    <div class="checkbox-group">
      <?php
      $all_courses = ['BCA', 'BSc CSIT', 'BIT', 'BBA', 'BBS', 'BIM', 'BHM', 'BASW'];
      foreach ($all_courses as $course) {
          $checked = in_array($course, $courses) ? 'checked' : '';
          echo "<label><input type='checkbox' name='course[]' value='$course' $checked> $course</label>";
      }
      ?>
    </div>

    

    <label>Duration (Years)</label>
    <select name="duration" required>
      <option value="" disabled <?php if (!$duration) echo "selected"; ?>>Select Duration</option>
      <?php for ($i = 1; $i <= 5; $i++) {
          $selected = ($duration == $i) ? 'selected' : '';
          echo "<option value=\"$i\" $selected>$i year" . ($i > 1 ? 's' : '') . "</option>";
      } ?>
    </select>

    <label>Scholarships</label>
    <textarea name="scholarships"><?php echo htmlspecialchars($scholarships); ?></textarea>

    <label>Description</label>
    <textarea name="description"><?php echo htmlspecialchars($description); ?></textarea>

    <button type="submit"><?php echo $id ? 'Update College' : 'Add College'; ?></button>
  </form>

  <p><a href="admin_dashboard.html">Back to Admin Dashboard</a></p>

<script>
  const form = document.getElementById('collegeForm');
  const messageDiv = document.getElementById('message');

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    messageDiv.textContent = '';
    messageDiv.style.color = '';

    const formData = new FormData(form);

    fetch(form.action, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        messageDiv.style.color = 'green';
        messageDiv.textContent = data.message;

        setTimeout(() => {
          window.location.href = "../admin/admin_dashboard.html";
        }, 1500);
      } else {
        messageDiv.style.color = 'red';
        if (data.errors && data.errors.length > 0) {
          messageDiv.innerHTML = data.errors.map(err => `<div>${err}</div>`).join('');
        } else {
          messageDiv.textContent = 'An error occurred.';
        }
      }
    })
    .catch(err => {
      messageDiv.style.color = 'red';
      messageDiv.textContent = 'Failed to submit form.';
      console.error(err);
    });
  });
</script>

</body>
</html>
