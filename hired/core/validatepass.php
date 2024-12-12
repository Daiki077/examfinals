<?php
require_once "dbConfig.php";
require_once "models.php";

function checkUsernameExistence($pdo, $username) {
    // Query updated to match a generic 'user_accounts' table
    $query = "SELECT 1 FROM users WHERE username = ?";
    $statement = $pdo->prepare($query);
    $statement->execute([$username]);

    // Return true if username exists
    return $statement->rowCount() > 0;
}

function validatePassword($password) {
    // Check if password meets criteria (minimum 8 characters, at least one uppercase letter, one lowercase letter, and one number)
    if (strlen($password) >= 8) {
        $hasLower = false;
        $hasUpper = false;
        $hasNumber = false;

        for ($i = 0; $i < strlen($password); $i++) {
            if (ctype_lower($password[$i])) {
                $hasLower = true;
            }
            if (ctype_upper($password[$i])) {
                $hasUpper = true;
            }
            if (ctype_digit($password[$i])) {
                $hasNumber = true;
            }

            // Return true if all conditions are met
            if ($hasLower && $hasUpper && $hasNumber) {
                return true;
            }
        }
    }
    return false;
}
?>