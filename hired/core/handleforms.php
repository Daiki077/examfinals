<?php
require_once 'dbconfig.php'; // Ensure this file initializes $pdo
require_once 'models.php';
require_once 'validatepass.php';

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registerButton'])) {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);
    $confirm_password = sanitizeInput($_POST['confirm_password']);
    $role = sanitizeInput($_POST['role']);
    $email = sanitizeInput($_POST['email_address']);

    if ($password !== $confirm_password) {
        $_SESSION['message'] = "Passwords do not match!";
        header("Location: register.php");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email) VALUES (:username, :password, :role, :email)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashed_password,
            ':role' => $role,
            ':email' => $email,
        ]);

        $_SESSION['message'] = "Registration successful! <a href='login.php'>Login here</a>";
        header("Location: login.php");
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        header("Location: login.php");
    }
    exit();
}

// Handle Job Post Creation (HR Only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['createJobPostButton'])) {
    if ($_SESSION['role'] !== 'hr') {
        header("Location: index.php");
        exit();
    }

    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);

    if (createJobPost($pdo, $title, $description, $_SESSION['user_id'])) {
        header("Location: index.php");
    } else {
        $_SESSION['message'] = "Error posting job. Please try again.";
        header("Location: create_job_post.php");
    }
    exit();
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['loginButton'])) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    $user = authenticateUser($pdo, $username, $password);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: ../index.php");
    } else {
        $_SESSION['message'] = "Invalid credentials!";
        header("Location: ../login.php");
    }
    exit();
}

// Handle Job Application
if (isset($_POST['applyJobButton'])) {
    // Get the job_post_id and user_id
    $job_post_id = $_POST['job_post_id'];
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
    $description = sanitizeInput($_POST['description']);  // Sanitize description

    $upload_dir = "../resumes"; // Directory to store resumes
    $upload_file = $_FILES['resume']; // Handle uploaded resume

    // Check if file is uploaded and valid
    if ($upload_file['error'] == UPLOAD_ERR_OK) {
        $file_name = basename($upload_file['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Only allow specific file types
        $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($file_ext, $allowed_exts)) {
            $_SESSION['message'] = "Invalid file type. Only PDF, JPG, and PNG are allowed.";
            header("Location: ../index.php?post_id=" . $job_post_id);
            exit();
        }

        // Generate unique file name
        $new_file_name = uniqid("resume_") . "." . $file_ext;
        $target_file = $upload_dir . "/" . $new_file_name;

        // Create the resume directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Move the uploaded file to the directory
        if (move_uploaded_file($upload_file['tmp_name'], $target_file)) {
            // Insert into the 'applications' table
            $query = "INSERT INTO applications (job_post_id, applicant_id, resume, description) 
                      VALUES (:job_post_id, :applicant_id, :resume, :description)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':job_post_id', $job_post_id);
            $stmt->bindParam(':applicant_id', $user_id);
            $stmt->bindParam(':resume', $new_file_name);
            $stmt->bindParam(':description', $description);

            // Execute the query
            if ($stmt->execute()) {
                $_SESSION['message'] = "Application submitted successfully!";
                header("Location: ../index.php?post_id=" . $job_post_id);
            } else {
                $_SESSION['message'] = "Failed to submit your application.";
                header("Location: ../apply_job.php?post_id=" . $job_post_id);
            }
        } else {
            $_SESSION['message'] = "Failed to upload resume.";
            header("Location: ../apply_job.php?post_id=" . $job_post_id);
        }
    } else {
        $_SESSION['message'] = "No file uploaded or there was an error with the file.";
        header("Location: ../apply_job.php?post_id=" . $job_post_id);
    }
}

// Handle Application Status Update
if (isset($_POST['action']) && isset($_POST['application_id'])) {
    $application_id = $_POST['application_id'];
    $action = $_POST['action'];
    $status = $action === 'accept' ? 'accepted' : 'rejected';
    $message = $action === 'accept' 
        ? 'Congratulations! You have been accepted for the position.' 
        : 'We are sorry, but your application has been rejected.';

    try {
        // Update application status
        $stmt = $pdo->prepare("UPDATE applications SET status = :status WHERE application_id = :application_id");
        $stmt->execute([
            ':status' => $status,
            ':application_id' => $application_id,
        ]);

        // Fetch applicant email
        $stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = (SELECT applicant_id FROM applications WHERE application_id = :application_id)");
        $stmt->execute([':application_id' => $application_id]);
        $applicant_email = $stmt->fetchColumn();

        // Send email notification
        mail($applicant_email, "Application Status Update", $message, "From: hr@company.com");
        header("Location: ../index.php?status=success");
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error updating application status: " . $e->getMessage();
        header("Location: ../viewprocess.php?status=error");
    }
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['messagebtn'])) {
    $message = htmlspecialchars(trim($_POST['message']));
    $hr_id = intval($_POST['hr_id']); // Get HR ID from the form input

    // Validate HR ID
    $queryCheckHr = "SELECT COUNT(*) FROM users WHERE user_id = ? AND role = 'hr'";
    $statementCheckHr = $pdo->prepare($queryCheckHr);
    $statementCheckHr->execute([$hr_id]);

    if ($statementCheckHr->fetchColumn() == 0) {
        $feedback = "Invalid HR selected.";
        header("Location: ../messagehr.php?feedback=" . urlencode($feedback));
        exit();
    } else {
        // Insert the message
        if (insertMessage($pdo, $_SESSION['user_id'], $hr_id, $message)) {
            $feedback = "Message sent successfully.";
            header("Location: ../messagehr.php?feedback=" . urlencode($feedback));
            exit();
        } else {
            $feedback = "Error sending message.";
            header("Location: ../messagehr.php?feedback=" . urlencode($feedback));
            exit();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_message'], $_POST['receiver_id'])) {
    $reply_message = htmlspecialchars(trim($_POST['reply_message']));
    $receiver_id = (int) $_POST['receiver_id'];
    $sender_id = $_SESSION['user_id']; // HR's ID

    // Insert reply into the messages table
    $query = "INSERT INTO messages (sender_id, receiver_id, message, timestamp) VALUES (:sender_id, :receiver_id, :message, NOW())";
    $stmt = $pdo->prepare($query);
    if ($stmt->execute([
        ':sender_id' => $sender_id,
        ':receiver_id' => $receiver_id,
        ':message' => $reply_message
    ])) {
        echo "Reply sent successfully.";
        header("Location: ../index.php?status=message_sent");
    } else {
        echo "Error sending reply.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['messagebtn']) || isset($_POST['reply_message'], $_POST['receiver_id'])) {
    $message = htmlspecialchars(trim($_POST['message'] ?? $_POST['reply_message']));
    $hr_id = isset($_POST['hr_id']) ? intval($_POST['hr_id']) : null; // HR ID if sending a message
    $receiver_id = isset($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : $hr_id; // Receiver ID for replies
    $sender_id = $_SESSION['user_id']; // Sender is the logged-in user (HR for replies)

    // If sending a message to HR
    if ($hr_id && $sender_id) {
        $queryCheckHr = "SELECT COUNT(*) FROM users WHERE user_id = ? AND role = 'hr'";
        $statementCheckHr = $pdo->prepare($queryCheckHr);
        $statementCheckHr->execute([$hr_id]);

        if ($statementCheckHr->fetchColumn() == 0) {
            $feedback = "Invalid HR selected.";
            header("Location: ../messagehr.php?feedback=" . urlencode($feedback));
            exit();
        }
    }

    // Insert the message (either a new message or a reply)
    $query = "INSERT INTO messages (sender_id, receiver_id, message, timestamp) VALUES (:sender_id, :receiver_id, :message, NOW())";
    $stmt = $pdo->prepare($query);
    if ($stmt->execute([
        ':sender_id' => $sender_id,
        ':receiver_id' => $receiver_id,
        ':message' => $message
    ])) {
        $feedback = isset($_POST['reply_message']) ? "Reply sent successfully." : "Message sent successfully.";
        header("Location: ../" . (isset($_POST['reply_message']) ? "view_messages" : "messagehr") . ".php?feedback=" . urlencode($feedback));
        exit();
    } else {
        $feedback = "Error sending message.";
        header("Location: ../" . (isset($_POST['reply_message']) ? "view_messages" : "messagehr") . ".php?feedback=" . urlencode($feedback));
        exit();
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_message'], $_POST['receiver_id'])) {
    $reply_message = htmlspecialchars(trim($_POST['reply_message']));
    $receiver_id = (int) $_POST['receiver_id']; 
    $sender_id = $_SESSION['user_id']; // HR's ID

    $query = "INSERT INTO messages (sender_id, receiver_id, message, timestamp) VALUES (:sender_id, :receiver_id, :message, NOW())";
    $stmt = $pdo->prepare($query);
    if ($stmt->execute([':sender_id' => $sender_id, ':receiver_id' => $receiver_id, ':message' => $reply_message])) {
        header("Location: ../view_message.php?status=message_sent");
        exit();
    } else {
        $_SESSION['message'] = "Error sending reply.";
        header("Location: ../view_message.php?status=error");
        exit();
    }
}
?>
