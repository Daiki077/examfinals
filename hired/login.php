
<?php
require_once 'core/dbconfig.php';
require_once 'core/models.php';
require_once 'core/handleforms.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        h1 {
            color: #1877f2;
            font-size: 36px;
            margin-bottom: 10px;
            
        }

        .container {
            width: 100%;
            max-width: 400px;
            background-color: #1e1e1e; 
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.7);
            text-align: center;
        }

        .container p {
            margin: 10px 0;
            color: #b0b0b0; 
        }

        .error-message {
            color: #ff5252; 
            background-color: #331111;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        label {
            display: block;
            text-align: left;
            font-weight: bold;
            margin: 10px 0 5px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 5px;
            background-color: #2a2a2a; 
            color: #ffffff; 
        }

        input:focus {
            outline: none;
            border: 2px solid #1877f2; 
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #1877f2; 
            color: #ffffff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 15px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3; 
        }

        a {
            color: #1877f2;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<header>
    <h1>Login</h1>
</header>

<div class="container">
    <!-- Display error message if credentials are invalid -->
    <?php if (isset($_SESSION['message'])): ?>
        <p class="error-message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
    <?php endif; ?>

    <form method="POST" action="core/handleforms.php">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <button type="submit" name="loginButton"> Login</button>
    </form>

    <p>Dont have an account? <a href="register.php">Register here</a></p>
</div>

</body>
