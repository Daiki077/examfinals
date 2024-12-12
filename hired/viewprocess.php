<?php
require_once 'core/dbconfig.php';
require_once 'core/models.php'; // Only include once

// Ensure the user is logged in and has an HR role
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'hr') {
    header("Location: index.php");
    exit();
}

// Fetch applicants' data
$query = "
    SELECT 
        a.application_id,
        a.resume,
        a.description AS applicant_contact,
        a.status,
        u.username AS applicant_name
    FROM 
        applications a
    INNER JOIN 
        users u 
    ON 
        a.applicant_id = u.user_id
    ORDER BY 
        a.application_id DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute();

// Correct use of PDO to fetch results
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>view the applicant</title>
    <style>
    
        body {
            font-family: Arial, sans-serif;
            background-color: #121212; 
            color: #ffffff; 
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #1e1e1e; 
            color: #ffffff; 
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        a {
            text-decoration: none;
            color: #007bff;
        }

        a:hover {
            color: #0056b3;
        }

 
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #1e1e1e; 
            color: #ffffff; 
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5);
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #2c2c2c; 
        }

        table th {
            background-color: #333333; 
        }

        table td a {
            color: #80d4ff; 
        }

        table td a:hover {
            color: #4fb3ff;
        }


        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            color: #ffffff;
            font-size: 14px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .btn-primary {
            background-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-success {
            background-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        p {
            margin: 10px 0;
        }

        p[style*="color: green;"] {
            background-color: #155724;
            padding: 10px;
            border-radius: 5px;
        }

        p[style*="color: red;"] {
            background-color: #721c24;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>


<div class="container">
    <?php
    // Display status messages based on URL parameters
    if (isset($_GET['status'])) {
        echo $_GET['status'] === 'success' 
            ? "<p style='color: green;'>Application processed successfully!</p>" 
            : "<p style='color: red;'>An error occurred while processing the application. Please try again.</p>";
    }
    ?>

    <a href="index.php" class="btn btn-primary">Back to Homepage</a>

    <table>
        <thead>
            <tr>
                <th>Applicant Name</th>
                <th>Contact Info</th>
                <th>Resume</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $row) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['applicant_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['applicant_contact']); ?></td>
                    <td>
                        <a href="uploads/<?php echo htmlspecialchars($row['resume']); ?>" target="_blank">link</a>
                    </td>
                    <td><?php echo ucfirst(htmlspecialchars($row['status'])); ?></td>
                    <td>
                        <?php if ($row['status'] == 'pending') { ?>
                            <form action="core/handleforms.php" method="POST" style="display:inline;">
                             <button type="submit" name="action" value="accept" class="btn btn-success" onclick="return confirm('Are you sure you want to accept this applicant?')">Accept</button>
                             <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>" />
                        </form>
                            <form action="core/handleforms.php" method="POST" style="display:inline;">
                                <button type="submit" name="action" value="reject" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this applicant?')">Reject</button>
                                <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>" />
                                </form>
                        <?php } else { ?>
                            <em>Already <?php echo ucfirst(htmlspecialchars($row['status'])); ?></em>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php
$pdo = null; // Close the PDO connection
?>
