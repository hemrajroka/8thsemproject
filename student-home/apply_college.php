<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Apply for College</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f7f9fc;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      padding: 40px 10px;
      margin: 0;
    }
    form {
      background: white;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      max-width: 400px;
      width: 100%;
    }
    h1 {
      text-align: center;
      margin-bottom: 25px;
      color: #2c3e50;
    }
    label {
      display: block;
      margin-bottom: 15px;
      font-weight: 600;
      color: #34495e;
    }
    input[type="text"],
    input[type="number"],
    select,
    input[type="file"] {
      width: 100%;
      padding: 10px 12px;
      border-radius: 5px;
      border: 1.8px solid #ccc;
      font-size: 1rem;
      transition: border-color 0.3s ease;
      box-sizing: border-box;
    }
    input[type="text"]:focus,
    input[type="number"]:focus,
    select:focus,
    input[type="file"]:focus {
      outline: none;
      border-color: #2980b9;
      box-shadow: 0 0 5px #2980b9;
    }
    button {
      width: 100%;
      padding: 12px;
      border: none;
      background-color: #2980b9;
      color: white;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-top: 10px;
    }
    button:hover {
      background-color: #1c5980;
    }
    .note {
      font-size: 0.9rem;
      color: #7f8c8d;
      margin-top: -10px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <form id="applicationForm" enctype="multipart/form-data">
    <h1>Apply for College</h1>

    <input type="hidden" name="college_id" id="college_id" value="" />

    <label for="applied_course">Course Applying For:</label>
    <input type="text" name="applied_course" id="applied_course" placeholder="Enter course name" required />

    <label for="entrance_rank">Entrance Rank:</label>
    <input type="number" name="entrance_rank" id="entrance_rank" placeholder="Your entrance exam rank" min="1" max="5000" required />

    <label for="plus_two_faculty">+12 Background Faculty:</label>
    <select name="plus_two_faculty" id="plus_two_faculty" required>
      <option value="" disabled selected>Select your +12 faculty</option>
      <option value="Science">Science</option>
      <option value="Management">Management</option>
      <option value="Humanities">Humanities</option>
      <option value="Commerce">Commerce</option>
      <option value="Engineering">Engineering</option>
      <option value="Others">Others</option>
    </select>

    <label for="marksheet">Upload +12 Marksheet (pdf, jpg, jpeg, png):</label>
    <input type="file" name="marksheet" id="marksheet" accept=".pdf,.jpg,.jpeg,.png" required />
    

    <button type="submit">Submit Application</button>
  </form>

  <script>
    // Get college_id from URL and set hidden field
    function getCollegeIdFromURL() {
      const params = new URLSearchParams(window.location.search);
      return params.get('college_id') || '';
    }
    document.getElementById('college_id').value = getCollegeIdFromURL();

    const form = document.getElementById('applicationForm');
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(form);

      fetch('/8thsem_project/student-home/submit_application.php', {
        method: 'POST',
        body: formData,
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert(data.message);
          window.location.href = '/8thsem_project/student-home/student_home.html';
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        alert('Error submitting application: ' + error.message);
      });
    });
  </script>
</body>
</html>
