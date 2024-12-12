<?php
require_once 'core/dbconfig.php';
require_once 'core/models.php';

// Start session and ensure user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch the recipients (users who are not the current logged-in user)
$stmt = $pdo->prepare("SELECT user_id, username FROM users WHERE user_id != :user_id");
$stmt->execute([':user_id' => $user_id]);
$recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize messages variable
$messages = null;

// Fetch chat messages if a recipient is selected
if (isset($_GET['recipient_id'])) {
    $recipient_id = intval($_GET['recipient_id']);

    // Fetch messages between the logged-in user and the selected recipient
    $stmt = $pdo->prepare("
        SELECT m.message, m.timestamp, u.username AS sender_name
        FROM messages m
        LEFT JOIN users u ON m.sender_id = u.user_id
        WHERE (m.sender_id = :user_id AND m.receiver_id = :recipient_id)
        OR (m.sender_id = :recipient_id AND m.receiver_id = :user_id)
        ORDER BY m.timestamp ASC
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':recipient_id' => $recipient_id
    ]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        // Insert message into the messages table
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, message)
            VALUES (:sender_id, :receiver_id, :message)
        ");
        $stmt->execute([
            ':sender_id' => $user_id,
            ':receiver_id' => $_POST['recipient_id'],
            ':message' => $message
        ]);

        // Redirect to reload messages
        header("Location: chat.php?recipient_id=" . $_POST['recipient_id']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatmate</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f5;
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .message-box {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
            overflow-y: auto;
            height: 300px;
            background: #f8f8f8;
        }
        .message {
            margin: 5px 0;
            line-height: 1.6;
        }
        .message .sender {
            font-weight: bold;
            color: #4CAF50;
        }
        textarea {
            width: 100%;
            height: 60px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: none;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Chat</h2>
    <form method="GET" action="chat.php">
        <label for="recipient_id">Select Recipient:</label>
        <select name="recipient_id" id="recipient_id" required>
            <option value="">Choose a recipient</option>
            <?php foreach ($recipients as $recipient): ?>
                <option value="<?php echo $recipient['user_id']; ?>" 
                    <?php echo (isset($_GET['recipient_id']) && $_GET['recipient_id'] == $recipient['user_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($recipient['username']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Start Chat</button>
    </form>

    <?php if (isset($_GET['recipient_id']) && $messages): ?>
        <div class="message-box">
            <?php foreach ($messages as $message): ?>
                <div class="message">
                    <span class="sender"><?php echo htmlspecialchars($message['sender_name']); ?>:</span>
                    <?php echo htmlspecialchars($message['message']); ?>
                    <span class="time" style="font-size: 0.8em; color: gray;">(<?php echo $message['timestamp']; ?>)</span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif (isset($_GET['recipient_id'])): ?>
        <p>No messages yet. Start the conversation!</p>
    <?php endif; ?>

    <?php if (isset($_GET['recipient_id'])): ?>
        <form method="POST" action="chat.php?recipient_id=<?php echo $_GET['recipient_id']; ?>">
            <input type="hidden" name="recipient_id" value="<?php echo (int)$_GET['recipient_id']; ?>">
            <textarea name="message" placeholder="Type your message here..." required></textarea>
            <button type="submit">Send</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
