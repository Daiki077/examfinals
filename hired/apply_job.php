<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job</title>
    <style>
        body {
            background-color: #121212;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #1e1e1e;
            padding: 20px;
            text-align: center;
        }

        header h1 {
            color: #2196F3;
            margin: 0;
        }

        nav {
            background-color: #333;
            display: flex;
            justify-content: center;
            padding: 10px;
        }

        nav a {
            color: #2196F3;
            margin: 0 15px;
            text-decoration: none;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        h2 {
            color: #2196F3;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        textarea, input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
            background-color: #333;
            color: white;
        }

        textarea:focus, input[type="file"]:focus {
            outline: 2px solid #2196F3;
        }

        button {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #1976D2;
        }
    </style>
</head>
<body>
<header>
    <h1>Enter Your Resume Below</h1>
</header>

<nav>
    <a href="index.php">Home</a>
    <a href="message_hr.php">Message HR</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <h2>Apply for a Job</h2>
    <form method="POST" action="core/handleforms.php" enctype="multipart/form-data">
        <input type="hidden" name="job_post_id" value="POST_ID_HERE">
        <div class="form-group">
            <label for="description">Number or Email:</label>
            <textarea name="description" id="description" required></textarea>
        </div>
        <div class="form-group">
            <label for="resume">Resume (PDF, JPG, PNG):</label>
            <input type="file" name="resume" id="resume" accept=".pdf, .jpg, .png" required>
        </div>
        <button type="submit" name="applyJobButton">Apply</button>
    </form>
</div>
</body>
</html>