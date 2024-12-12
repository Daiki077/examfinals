<?php
require_once 'core/dbconfig.php';
require_once 'core/models.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'hr') {
    $_SESSION['error'] = "Unauthorized access.";
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($title) || empty($description)) {
        $error_message = "All fields are required.";
    } else {
        try {
       
            $stmt = $pdo->prepare("INSERT INTO job_posts (title, description, hr_id) VALUES (:title, :description, :hr_id)");
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':hr_id' => $_SESSION['user_id']
            ]);

            $_SESSION['success'] = "Job post created successfully.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error_message = "Failed to create the job post. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Job Post</title>
    <link rel="stylesheet" href="styles.css">
</head>
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

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
            background-color: #333;
            color: white;
        }

        input[type="text"]:focus, textarea:focus {
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

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-danger {
            background-color: #d32f2f;
            color: white;
        }

        .btn {
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background-color: #2196F3;
            color: white;
            border: none;
            display: block;
            margin-bottom: 15px;
        }

        .btn-primary:hover {
            background-color: #1976D2;
        }

        .btn-success {
            background-color: #1976D2;
            color: white;
        }

        .btn-success:hover {
            background-color:  #1976D2;
        }
    </style>
<body>

<header>
    <h1>Create Job Post</h1>
</header>

<div class="container">
    <a href="index.php" class="btn btn-primary">Back to Homepage</a>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div>
            <label for="title">Job Title:</label>
            <input type="text" id="title" name="title" required>
        </div>
        
        <div>
            <label for="description">Job Description:</label>
            <textarea id="description" name="description" required></textarea>
        </div>
        
        <button type="submit" class="btn btn-success">Post Job</button>
    </form>
</div>

</body>
</html>
