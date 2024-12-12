
<?php
require_once 'core/dbconfig.php';
require_once 'core/models.php';


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
 
        body {
            font-family: Arial, sans-serif;
            background-color: #121212; 
            color: #ffffff; 
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 400px;
            background-color: #1e1e1e; 
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.7);
        }

        h2 {
            color: #1877f2; 
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 5px;
        }

        input, select {
            background-color: #2a2a2a;
            color: #ffffff; 
        }

        input:focus, select:focus {
            outline: none;
            border: 2px solid #1877f2; 
        }

        button {
            background-color: #1877f2; 
            color: #ffffff;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3; 
        }

        p {
            text-align: center;
        }

        p a {
            color: #1877f2;
            text-decoration: none;
        }

        p a:hover {
            text-decoration: underline;
        }

        .error {
            color: #ff5252; 
            background-color: #331111;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }

        .success {
            color: #4CAF50; 
            background-color: #1b331b;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Register</h2>
    <?php if (isset($feedback)): ?>
        <p class="<?php echo strpos($feedback, 'successful') !== false ? 'success' : 'error'; ?>">
            <?php echo $feedback;?>
        </p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <label for="role">Role:</label>
        <select id="role" name="role" required>
            <option value="applicant">Applicant</option>
            <option value="hr">HR</option>
        </select>

        <label for="email_address">Email Address:</label>
        <input type="email" id="email_address" name="email_address" required>

        <button type="submit" name="registerButton">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>
