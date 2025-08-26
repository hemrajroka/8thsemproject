<?php
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /6thsem_project/login/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>All Colleges</title>
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

    .main-content {
      margin-left: 220px;
      padding: 2rem;
      width: calc(100% - 220px);
    }

    .search-container {
      display: flex;
      gap: 10px;
      margin-bottom: 2rem;
    }

    #search-input {
      flex: 1;
      padding: 0.5rem;
      font-size: 1rem;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    #search-btn {
      padding: 0.5rem 1rem;
      font-size: 1rem;
      background-color: #1c3a66;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .grid-table {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
    }

    .college-card {
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 10px;
      padding: 1rem;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .college-card h3 {
      color: #1c3a66;
      margin-bottom: 0.5rem;
    }

    .college-card div {
      margin-bottom: 0.3rem;
    }

    .label {
      font-weight: bold;
      color: #274c87;
    }

    .actions {
      margin-top: 0.8rem;
      display: flex;
      justify-content: space-between;
      gap: 0.5rem;
    }

    .actions button {
      flex: 1;
      padding: 0.4rem 0.6rem;
      font-size: 0.9rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s ease;
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
      .sidebar {
        position: static;
        width: 100%;
        height: auto;
        flex-direction: row;
        justify-content: space-around;
      }

      .main-content {
        margin-left: 0;
        width: 100%;
        padding: 1rem;
      }

      .grid-table {
        grid-template-columns: 1fr;
      }

      .search-container {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

  <div class="sidebar">
    
    <a href="/6thsem_project/student-home/student_home.html">Recommendations</a>
    <a href="/6thsem_project/student-home/applications.php">Application Status</a>
    <a href="/6thsem_project/student-home/view_other_universities.php">Universities</a>
    <a href="/6thsem_project/student-home/profile_update.php">Profile</a>
 <a href="/6thsem_project/login/logout.php">Logout</a>
  </div>

  <div class="main-content">
    <div class="search-container">
      <input type="text" id="search-input" placeholder="Search by name, course, university, location..." />
      <button id="search-btn">Search</button>
    </div>

    <div class="grid-table" id="college-container">
      <p>Loading colleges...</p>
    </div>
  </div>

  <script>
    const container = document.getElementById("college-container");
    const searchInput = document.getElementById("search-input");
    const searchBtn = document.getElementById("search-btn");
    let allColleges = [];

    function renderColleges(data) {
      container.innerHTML = "";
      if (data.length === 0) {
        container.innerHTML = "<p>No colleges found.</p>";
        return;
      }

      data.forEach(college => {
        const card = document.createElement("div");
        card.className = "college-card";
        card.innerHTML = `
          <h3>${college.name}</h3>
          <div><span class="label">Course:</span> ${college.course}</div>
          <div><span class="label">University:</span> ${college.university}</div>
          <div><span class="label">Location:</span> ${college.location}</div>
          <div><span class="label">Phone:</span> ${college.phone_number}</div>
          <div><span class="label">Scholarships:</span> ${college.scholarships}</div>
          <div><span class="label">Duration:</span> ${college.duration}</div>
          <div class="actions">
            <button class="view-btn" onclick="viewDetails(${college.id})">View Details</button>
            <button class="apply-btn" onclick="applyCollege(${college.id})">Apply Now</button>
          </div>
        `;
        container.appendChild(card);
      });
    }

    function fetchColleges() {
      fetch("all_colleges.php")
        .then(res => res.json())
        .then(data => {
          allColleges = data;
          renderColleges(allColleges);
        })
        .catch(err => {
          container.innerHTML = "<p>Error loading data.</p>";
        });
    }

    function handleSearch() {
      const keyword = searchInput.value.toLowerCase();
      const filtered = allColleges.filter(college =>
        (college.name + college.course + college.university + college.location)
          .toLowerCase()
          .includes(keyword)
      );
      renderColleges(filtered);
    }

    searchInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") handleSearch();
    });

    searchBtn.addEventListener("click", handleSearch);

    function viewDetails(id) {
      window.location.href = `view_college_details.php?id=${id}`;
    }

    function applyCollege(id) {
      window.location.href = `apply_college.php?college_id=${id}`;
    }

    fetchColleges();
  </script>
</body>
</html>
