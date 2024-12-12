<?php
require_once 'core/dbconfig.php';
require_once 'core/handleforms.php';
require_once 'core/models.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the user role and ID from the session
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

if ($role == 'applicant') {
    // Get available job posts for an applicant
    $job_posts = getAvailableJobPosts($pdo);

    // Get the accepted and rejected jobs for the applicant
    $accepted_jobs = getAcceptedJobs($pdo, $user_id);
    $rejected_jobs = getRejectedJobs($pdo, $user_id);
}
if ($role == 'hr') {
    // Get available job posts for an applicant
    $job_posts = getAvailableJobPosts($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>index</title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
    
        body {
            font-family: Arial, sans-serif;
            background-color: #121212; 
            color: #ffffff; 
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        h1, h2, h3 {
            color: #1877f2; 
            margin: 20px 0;
        }

        a {
            text-decoration: none;
            color: #1877f2; 
        }

        a:hover {
            color: #0056b3;
        }

      
        header {
            padding: 10px;
            text-align: center;
        }

        header h1 {
            margin: 0;
        }

        nav {
            background-color: #333333; 
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        nav a {
            margin: 0 15px;
            color: #ffffff;
            font-size: 16px;
        }

        nav a:hover {
            color: #1877f2;
        }

    
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #1e1e1e; 
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.7);
            text-align: center;
        }

  
        .role-message {
            margin-bottom: 20px;
            font-size: 18px;
            color: #cccccc;
        }

      
        .job-posts-board {
            margin-top: 20px;
            text-align: left;
        }

        .job-posts-board table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .job-posts-board th, .job-posts-board td {
            padding: 12px;
            border: 1px solid #444444; 
            text-align: left;
        }

        .job-posts-board th {
            background-color: #333333; 
            color: #ffffff;
        }

        .job-posts-board td {
            background-color: #1e1e1e;
        }

        .job-posts-board a {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            background-color: #333333; 
            text-align: center;
            color: #ffffff;
        }

        .job-posts-board a:hover {
            background-color: #444444; 
        }

       
        ul {
            list-style: none;
            padding-left: 0;
            text-align: left;
        }

        ul li {
            background-color: #333333; 
            margin: 5px 0;
            padding: 10px;
            border-radius: 4px;
        }


        button {
            background-color: #1877f2; 
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3; 
        }

        .alert {
            background-color: #f44336;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            nav a {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
<body>

<header>
    <?php if ($role == 'hr'): ?>
        <h1>Welcome HR <?php echo $_SESSION['username']; ?></h1>
    <?php elseif ($role == 'applicant'): ?>
        <h1>Welcome Applicant <?php echo $_SESSION['username']; ?></h1>
    <?php endif; ?>
</header>

<nav>
    <?php if ($role == 'hr'): ?>
        <a href="createpost.php">Create Job Post</a>
        <a href="viewprocess.php">View Process</a>
        <a href="view_message.php">View Messages</a>
    <?php elseif ($role == 'applicant'): ?>
        <a href="messagehr.php">Message HR</a>
    <?php endif; ?>
    <a href="core/logout.php">Logout</a>
</nav>

<div class="container">
    <div class="role-message">
        <?php if ($role == 'hr'): ?>
            <p>You are logged in as HR.</p>
        <?php elseif ($role == 'applicant'): ?>
            <p>You are logged in as an Applicant. You can apply for jobs and message HR representatives.</p>
        <?php endif; ?>
    </div>
<?php if ($role == 'applicant'): ?>
    <div class="job-posts-board">
        <h2>Available Job Posts</h2>
        <?php if (empty($job_posts)): ?>
            <p>No job posts available at the moment.</p>
        <?php else: ?>
            <div class="job-cards">
                <?php foreach ($job_posts as $job): ?>
                    <div class="job-card">
                        <h3><?= htmlspecialchars($job['title']); ?></h3>
                        <p><?= htmlspecialchars($job['description']); ?></p>
                        <a href="apply_job.php?job_post_id=<?= $job['job_id']; ?>" class="apply-button">Apply Now</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php if ($role == 'hr'): ?>
<div class="job-posts-board">
    <h2>Your Job Posts</h2>
    <?php if (empty($job_posts)): ?>
        <p>No job posts created yet.</p>
    <?php else: ?>
        <div class="job-cards">
            <?php foreach ($job_posts as $job): ?>
                <div class="job-card">
                    <h3><?= htmlspecialchars($job['title']); ?></h3>
                    <p><?= htmlspecialchars($job['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

    <!-- Display Accepted Jobs -->
    <div class="accepted-jobs">
    <h3>Accepted Job Titles</h3>
    <?php if (empty($accepted_jobs)): ?>
        <p>No accepted jobs yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($accepted_jobs as $job): ?>
                <li><?= htmlspecialchars($job['title']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<!-- Rejected Jobs -->
<div class="rejected-jobs">
    <h3>Rejected Job Titles</h3>
    <?php if (empty($rejected_jobs)): ?>
        <p>No rejected jobs yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($rejected_jobs as $job): ?>
                <li><?= htmlspecialchars($job['title']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

</body>
</html>
