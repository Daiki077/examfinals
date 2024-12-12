<?php
require_once 'dbconfig.php';
require_once 'handleforms.php';

function handleJobApplication($job_post_id, $user_id, $description, $upload_dir, $pdo) {
    // Validate description
    if (empty($description)) {
        return "Description cannot be empty.";
    }

    // Validate and handle file upload
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['resume']['tmp_name'];
        $file_name = basename($_FILES['resume']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Ensure the uploaded file is a PDF
        if ($file_ext != 'pdf') {
            return "Only PDF files are allowed.";
        }

        // Check for file size (maximum size: 5MB)
        if ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
            return "File size exceeds the maximum allowed size of 5MB.";
        }

        // Generate a unique file name and move to the 'resumes' directory
        $new_file_name = uniqid("resume_") . ".pdf";
        $target_file = $upload_dir . "/" . $new_file_name;
        if (!move_uploaded_file($file_tmp, $target_file)) {
            return "Failed to upload resume.";
        }

        $resume_path = $target_file;
    } else {
        return "Resume file is required.";
    }

    // Check if the job post exists
    $stmt = $pdo->prepare("SELECT * FROM job_posts WHERE job_id = :job_post_id");
    $stmt->bindParam(':job_post_id', $job_post_id);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        return "Job post not found.";
    }

    // Handle job application
    $stmt = $pdo->prepare("
        INSERT INTO applications (job_post_id, applicant_id, resume, description, status)
        VALUES (:job_post_id, :applicant_id, :resume, :description, 'pending')
    ");

    $stmt->bindParam(':job_post_id', $job_post_id);
    $stmt->bindParam(':applicant_id', $user_id);
    $stmt->bindParam(':resume', $resume_path);
    $stmt->bindParam(':description', $description);

    if ($stmt->execute()) {
        return true; // Success
    } else {
        return "Failed to submit application.";
    }
}


function saveApplication($job_post_id, $applicant_id, $resume, $description, $pdo) {
    $stmt = $pdo->prepare("INSERT INTO applications (job_post_id, applicant_id, resume, description) VALUES (:job_post_id, :applicant_id, :resume, :description)");

    // Bind parameters
    $stmt->bindParam(':job_post_id', $job_post_id, PDO::PARAM_INT);
    $stmt->bindParam(':applicant_id', $applicant_id, PDO::PARAM_INT);
    $stmt->bindParam(':resume', $resume, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);

    // Execute the statement
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Get recipients based on role
function getRecipients($role, $user_id, $pdo) {
    $recipient_role = ($role === 'hr') ? 'applicant' : 'hr';

    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = ? AND id != ?");
    $stmt->bind_param("si", $recipient_role, $user_id);
    $stmt->execute();
    $recipients = $stmt->get_result();
    $stmt->close();

    return $recipients;
}

// Fetch messages between users
function getMessages($user_id, $recipient_id, $pdo) {
    $messages_query = "
        SELECT m.id, m.sender_id, m.receiver_id, m.message, m.created_at, u.username AS sender_name
        FROM messages m
        INNER JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? OR m.receiver_id = ?)
        AND (m.sender_id = ? OR m.receiver_id = ?)
        ORDER BY m.created_at ASC";
        
    $chat_stmt = $pdo->prepare($messages_query);
    $chat_stmt->bind_param("iiii", $user_id, $user_id, $recipient_id, $recipient_id);
    $chat_stmt->execute();
    $messages = $chat_stmt->get_result();
    $chat_stmt->close();

    return $messages;
}

// Sanitize user input
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

function addUser($pdo, $username, $password, $confirm_password, $hashed_password, $email, $role = 'applicant') {
    // Check if the username already exists
    $queryCheck = "SELECT COUNT(*) FROM users WHERE username = ?";
    $statementCheck = $pdo->prepare($queryCheck);
    $statementCheck->execute([$username]);
    if ($statementCheck->fetchColumn() > 0) {
        return ["statusCode" => "400", "message" => "Username already exists!"];
    }

    // Validate passwords match
    if ($password !== $confirm_password) {
        return ["statusCode" => "400", "message" => "Passwords do not match!"];
    }

    // Validate password format (assuming validatePassword is defined elsewhere)
    if (!validatePassword($password)) {
        return ["statusCode" => "400", "message" => "Invalid password format!"];
    }

    // Insert new user into the users table
    $queryInsert = "INSERT INTO users (username, password, role, email) VALUES (?, ?, ?, ?)";
    $statementInsert = $pdo->prepare($queryInsert);
    $executeInsert = $statementInsert->execute([$username, $hashed_password, $role, $email]);

    if ($executeInsert) {
        return ["statusCode" => "200", "message" => "Successfully registered applicant!"];
    }

    return ["statusCode" => "400", "message" => "An error occurred while registering."];
}


// Create a new job post
function createJobPost($pdo, $title, $description, $hr_id) {
    $stmt = $pdo->prepare("INSERT INTO job_posts (title, description, hr_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $title, $description, $hr_id);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

function authenticateUser($pdo, $username, $password) {
    // Prepare the statement
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    
    // Bind the parameter and execute
    $stmt->execute([':username' => $username]);

    // Fetch the user record
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the password
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }

    return null;
}
function getJobApplications($pdo) {
    // Fetch all pending job applications
    $stmt = $pdo->prepare(
        "SELECT a.application_id, jp.title AS job_title, u.username AS applicant_name, a.status 
         FROM applications a
         JOIN job_posts jp ON a.job_post_id = jp.job_id
         JOIN users u ON a.applicant_id = u.user_id
         WHERE a.status = 'pending'"
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

// Get available job posts
function getAvailableJobPosts($pdo) {
    $stmt = $pdo->prepare("SELECT job_id, title, description FROM job_posts");
    $stmt->execute();
    return $stmt->fetchall();
}

function getAcceptedJobs($pdo, $applicant_id) {
    $stmt = $pdo->prepare("
        SELECT jp.job_id, jp.title
        FROM applications AS a
        JOIN job_posts AS jp ON a.job_post_id = jp.job_id
        WHERE a.applicant_id = :applicant_id AND a.status = 'accepted'
    ");
    $stmt->bindParam(':applicant_id', $applicant_id, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($result)) {
        error_log("No accepted jobs found for applicant_id: " . $applicant_id);
    }
    return $result;
}

// Fetch rejected jobs
function getRejectedJobs($pdo, $applicant_id) {
    $stmt = $pdo->prepare("
        SELECT jp.job_id, jp.title
        FROM applications AS a
        JOIN job_posts AS jp ON a.job_post_id = jp.job_id
        WHERE a.applicant_id = :applicant_id AND a.status = 'rejected'
    ");
    $stmt->bindParam(':applicant_id', $applicant_id, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($result)) {
        error_log("No rejected jobs found for applicant_id: " . $applicant_id);
    }
    return $result;
}

// Insert a message
function insertMessage($pdo, $sender_id, $receiver_id, $message) {
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (:sender_id, :receiver_id, :message)");

    $stmt->bindParam(':sender_id', $sender_id, PDO::PARAM_INT);
    $stmt->bindParam(':receiver_id', $receiver_id, PDO::PARAM_INT);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);

    // Try to execute and check if the message was inserted successfully
    if ($stmt->execute()) {
        return "Message sent successfully.";
    } else {
        return "Failed to send the message. Please try again.";
    }
}

function getmessage($pdo, $user_id, $recipient_id) {
    $stmt = $pdo->prepare(
        "SELECT m.messages_id, m.sender_id, m.receiver_id, m.message, m.timestamp, u.username AS sender_name
        FROM messages m
        INNER JOIN users u ON m.sender_id = u.user_id
        WHERE (m.sender_id = :user_id AND m.receiver_id = :recipient_id) OR (m.sender_id = :recipient_id AND m.receiver_id = :user_id)
        ORDER BY m.timestamp ASC"
    );

    // Bind parameters
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':recipient_id', $recipient_id, PDO::PARAM_INT);
    
    // Execute and fetch messages
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function sendMessage($sender_id, $receiver_id, $message_text, $pdo) {
    // Prepare the SQL query to insert the message into the messages table
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message_text);

    if ($stmt->execute()) {
        return "Message sent successfully.";
    } else {
        return "Failed to send the message. Please try again.";
    }
}


?>