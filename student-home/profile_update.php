<?php
session_start();
require('../db_connection/db_connection.php');

if (!isset($_SESSION['studentID'])) {
    header("Location: login.php");
    exit();
}

$studentID = $_SESSION['studentID'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $university = trim($_POST['university'] ?? '');
    $courses = isset($_POST['course']) ? $_POST['course'] : [];

    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    if (!$fullname || !$gender || !$email || !$city || !$qualification || !$university || empty($courses)) {
        $error = "Please fill in all required fields and select at least one course.";
    } else {
        if ($old_password || $new_password) {
    if (!$old_password || !$new_password) {
        $error = "To change password, both old and new password fields must be filled.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        // Verify old password
        $stmt = $conn->prepare("SELECT password FROM students WHERE id = ?");
        $stmt->bind_param("i", $studentID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row || !password_verify($old_password, $row['password'])) {
            $error = "Old password is incorrect.";
        }
    }
}


        if (!$error) {
            $course_json = json_encode($courses);

            if ($old_password && $new_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE students SET fullname=?, gender=?, email=?, city=?, qualification=?, university=?, course=?, password=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssssi", $fullname, $gender, $email, $city, $qualification, $university, $course_json, $hashed_password, $studentID);
            } else {
                $sql = "UPDATE students SET fullname=?, gender=?, email=?, city=?, qualification=?, university=?, course=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssi", $fullname, $gender, $email, $city, $qualification, $university, $course_json, $studentID);
            }

            if ($stmt->execute()) {
                header("Location: student_home.html");
                exit();
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}

// Fetch current student data
$stmt = $conn->prepare("SELECT fullname, gender, email, city, qualification, university, course FROM students WHERE id=?");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $student = $result->fetch_assoc();
    $selected_courses = json_decode($student['course'], true) ?: [];
} else {
    die("Student profile not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Profile</title>
<style>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
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
  .main-content {
    margin-left: 220px;
    padding: 2rem;
    width: calc(100% - 220px);
  }
  form {
    background: white;
    padding: 20px 25px;
    border-radius: 8px;
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
    max-width: 500px;
    margin: auto;
  }
  label {
    display: block;
    margin-top: 12px;
    font-weight: bold;
  }
  input[type="text"],
  input[type="email"],
  select,
  input[type="password"] {
    width: 100%;
    padding: 8px 10px;
    margin-top: 6px;
    border: 1.5px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 1rem;
  }
  .checkbox-group label {
    font-weight: normal;
    margin-right: 15px;
  }
  button {
    width: 100%;
    padding: 12px;
    margin-top: 20px;
    background-color: #2980b9;
    color: white;
    font-size: 1.1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
  }
  button:hover {
    background-color: #1c5980;
  }
  .error {
    color: red;
    margin-top: 15px;
    font-weight: bold;
    text-align: center;
  }
</style>
</head>
<body>

<div class="sidebar">
  <a href="/6thsem_project/student-home/student_home.html">Recommendations</a>
  <a href="/6thsem_project/student-home/applications.php">Application Status</a>
  <a href="/6thsem_project/student-home/view_other_universities.php">Universities</a>
  <a href="/6thsem_project/student-home/profile_update.php">Profile</a>
  <a href="/6thsem_project/login/login.html">Logout</a>
</div>

<div class="main-content">

<?php if ($error): ?>
  <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="">
  <label>Full Name</label>
  <input type="text" name="fullname" value="<?= htmlspecialchars($student['fullname']) ?>" required>

  <label>Gender</label>
  <select name="gender" required>
    <option value="Male" <?= $student['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
    <option value="Female" <?= $student['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
  </select>

  <label>Email</label>
  <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>

  <label>City</label>
  <input type="text" name="city" value="<?= htmlspecialchars($student['city']) ?>" required>

  <label>Qualification</label>
  <input type="text" name="qualification" value="<?= htmlspecialchars($student['qualification']) ?>" required>

  <label>University</label>
  <select name="university" id="university" required>
    <option value="" disabled <?= !$student['university'] ? 'selected' : '' ?>>Select University</option>
    <?php
    $unis = ['Tribhuvan University', 'Pokhara University', 'Kathmandu University', 'Purbanchal University'];
    foreach ($unis as $uni) {
      $selected = ($student['university'] === $uni) ? 'selected' : '';
      echo "<option value=\"$uni\" $selected>$uni</option>";
    }
    ?>
  </select>

  <label>Courses (Select one or more)</label>
  <div class="checkbox-group">
    <label><input type="checkbox" name="course[]" value="BCA" <?= in_array("BCA", $selected_courses) ? 'checked' : '' ?>> BCA</label>
    <label><input type="checkbox" name="course[]" value="BIT" <?= in_array("BIT", $selected_courses) ? 'checked' : '' ?>> BIT</label>
    <label><input type="checkbox" name="course[]" value="BSc CSIT" <?= in_array("BSc CSIT", $selected_courses) ? 'checked' : '' ?>> BSc CSIT</label>
    <label><input type="checkbox" name="course[]" value="BBA" <?= in_array("BBA", $selected_courses) ? 'checked' : '' ?>> BBA</label>
    <label><input type="checkbox" name="course[]" value="BBS" <?= in_array("BBS", $selected_courses) ? 'checked' : '' ?>> BBS</label>
  </div>

  <hr style="margin: 20px 0;">


<input type="text" name="fakeusernameremembered" style="display:none">
<input type="password" name="fakepasswordremembered" style="display:none">

<label>Old Password</label>
<input type="password" name="old_password" autocomplete="new-password">

<label>New Password</label>
<input type="password" name="new_password" autocomplete="new-password">


  <button type="submit">Update Profile</button>
</form>
</div>

</body>
</html>