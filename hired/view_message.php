<?php
require_once 'core/dbconfig.php';
require_once 'core/models.php';

// Ensure HR is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'hr') {
    header("Location: index.php");
    exit();
}

$hr_id = $_SESSION['user_id']; // Get the logged-in HR's user ID

// Fetch messages for the HR
$query = "
    SELECT 
        m.message,
        m.timestamp AS created_at,
        u.username AS sender_name,
        m.sender_id
    FROM 
        messages m
    INNER JOIN 
        users u 
    ON 
        m.sender_id = u.user_id
    WHERE 
        m.receiver_id = :receiver_id
    ORDER BY 
        m.timestamp DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute([':receiver_id' => $hr_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>view message</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #121212; /* Dark background */
        color: #fff; /* White text */
        margin: 0;
        padding: 20px;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
        background-color: #1f1f1f;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }

    h1 {
        text-align: center;
        font-size: 24px;
        margin-bottom: 20px;
        color: #2196F3;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    th, td {
        padding: 12px 15px;
        border: 1px solid #444; 
        text-align: left;
    }

    th {
        background-color: #333; 
        color: #1877f2; 
    }

    tr:nth-child(even) {
        background-color: #2c2c2c; 
    }

    tr:hover {
        background-color: #333; 
    }

    .reply {
        margin-top: 15px;
        padding: 10px;
        background-color: #2c2c2c; 
        border-left: 5px solid #1877f2; 
        margin-left: 20px;
        font-size: 14px;
        color: #ddd; 
    }

    .reply p {
        margin: 0;
    }

    .reply small {
        font-size: 12px;
        color: #bbb; 
    }

    .btn {
        padding: 8px 15px;
        background-color: #1877f2; 
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 14px;
        border: none;
        cursor: pointer;
        margin-top: 10px;
    }

    .btn:hover {
        background-color: #1877f2; 
    }

    textarea {
        width: 100%;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #444; 
        font-size: 14px;
        margin-top: 10px;
        margin-bottom: 10px;
        background-color: #333; 
        color: #fff; 
    }

    .message-box {
        margin-top: 20px;
    }

    .message-box .form-group {
        margin-bottom: 10px;
    }

    .message-box textarea {
        height: 100px;
    }

    a {
        color:#1877f2; 
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<div class="container">
    <?php if (count($messages) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Message</th>
                    <th>Sent At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['sender_name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <!-- Form to reply -->
                            <form action="core/handleforms.php" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="send_reply">
                                <input type="hidden" name="receiver_id" value="<?php echo $row['sender_id']; ?>">
                                <textarea name="reply_message" placeholder="Write your reply..." required></textarea>
                                <button type="submit" class="btn btn-primary">Reply</button>
                            </form>

                            <!-- Fetch and display HR replies -->
                            <?php
                            // Fetch replies from HR to this applicant's message
                            $query_replies = "
                                SELECT 
                                    m.message,
                                    m.timestamp AS created_at,
                                    u.username AS sender_name
                                FROM 
                                    messages m
                                INNER JOIN 
                                    users u
                                ON 
                                    m.sender_id = u.user_id
                                WHERE 
                                    m.receiver_id = :receiver_id
                                    AND m.sender_id = :sender_id
                                ORDER BY 
                                    m.timestamp ASC
                            ";
                            $stmt_replies = $pdo->prepare($query_replies);
                            $stmt_replies->execute([
                                ':receiver_id' => $hr_id,
                                ':sender_id' => $row['sender_id'] // Get replies from the same applicant
                            ]);
                            $replies = $stmt_replies->fetchAll(PDO::FETCH_ASSOC);

                            if (count($replies) > 0):
                                foreach ($replies as $reply):
                            ?>
                                    <div class="reply">
                                        <strong>Reply from HR:</strong><br>
                                        <p><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                                        <small>Sent at: <?php echo htmlspecialchars($reply['created_at']); ?></small>
                                    </div>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No messages from applicants yet.</p>
    <?php endif; ?>

    <br>
    <a href="index.php" class="btn btn-primary">Back to Homepage</a>
</div>

</body>
</html>