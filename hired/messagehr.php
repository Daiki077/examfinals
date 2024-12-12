<?php
require_once 'core/dbconfig.php';
require_once 'core/models.php';
require_once 'core/models.php';

if ($_SESSION['role'] != 'applicant') {
    header("Location: index.php");
    exit();
}

$feedback = "";

$query = "SELECT user_id, username FROM users WHERE role = 'hr'";
$stmt = $pdo->query($query);
$hrs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message HR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212; 
            color: #e0e0e0; 
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background-color: #1e1e1e; 
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            font-size: 24px;
            color: #ffffff; 
        }

        label {
            font-size: 16px;
            margin-bottom: 8px;
            display: block;
            color: #e0e0e0; 
        }

        textarea {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #555; 
            background-color: #333; 
            color: #e0e0e0; 
            margin-bottom: 20px;
            resize: vertical;
        }

        select {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #555; 
            background-color: #333; 
            color: #e0e0e0; 
        }

        button {
    background-color: #007bff; 
    color: white;
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-top: 10px; 
}

    button:hover {
    background-color: #0056b3;
}

        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
            text-decoration: none;
            color: #0056b3; 
        }

        a:hover {
            color: #388E3C; 
        }

        p {
            text-align: center;
            color: #e0e0e0; 
        }
    </style>
</head>
<body>


<div class="container">
    <!-- Feedback Message -->
    <?php if (empty($hrs)): ?>
    <p>No HRs are available to message at this time.</p>
<?php else: ?>
    <form method="POST" action="core/handleforms.php">
    <label for="message">Message:</label>
    <textarea id="message" name="message" rows="4" required></textarea>
    
    <!-- Dropdown to select HR -->
    <label for="hr_id">Select HR:</label>
    <select name="hr_id" id="hr_id" required>
        <?php foreach ($hrs as $hr): ?>
            <option value="<?php echo $hr['user_id']; ?>"><?php echo htmlspecialchars($hr['username']); ?></option>
        <?php endforeach; ?>
    </select>
    
    <button type="submit" name="messagebtn">Send Message</button>
</form>
<?php endif; ?>

    <a href="index.php">Back to Homepage</a>
</div>

</body>
</html>

