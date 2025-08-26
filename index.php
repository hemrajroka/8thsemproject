<!-- index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>College Recommendation System - Nepal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            color: #333;
            text-align: center;
            padding: 60px 20px;
        }
        h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        p {
            font-size: 1.1em;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            background-color: #4caf50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .description {
            max-width: 600px;
            margin: 30px auto;
            font-size: 1em;
            line-height: 1.6;
            color: #555;
        }
        footer {
            margin-top: 40px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>

    <h1>College Recommendation System</h1>
    <p>Find the best colleges across Nepal based on your preferences.</p>

    <a class="btn" href="login/login.html">Login</a>
    <a class="btn" href="student_register/student_register.html">Register</a>

    <div class="description">
        <p>
            This system helps students in Nepal find suitable colleges based on their preferences like faculty, location, entrance rank, and budget. 
            Begin your academic journey by registering and getting personalized college recommendations today.
        </p>
    </div>

    <footer>
        &copy; <?php echo date("Y"); ?> College Recommendation System Nepal
    </footer>

</body>
</html>
