<?php
session_start();

// Check if user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .dashboard-header {
            background-color: #007bff;
            color: white;
            padding: 1rem;
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            margin: 0;
        }

        .user-info {
            float: right;
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .filters {
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .search-bar {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .job-posting {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .job-posting h3 {
            margin-top: 0;
            color: #007bff;
        }

        .job-details {
            margin: 15px 0;
        }

        .application-form {
            margin-top: 15px;
        }

        .application-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 10px;
        }

        .apply-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .apply-button:hover {
            background-color: #218838;
        }

        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        .no-jobs {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .job-meta {
            display: flex;
            gap: 20px;
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($_SESSION['fname']); ?> 
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
            <h1>Student Dashboard</h1>
        </div>
    </div>

    <div class="container">
        <div class="filters">
            <input type="text" class="search-bar" placeholder="Search for jobs..." id="searchJobs">
        </div>

        <div id="job-list">
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "fbla";

            // Connect to database
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                echo "<p style='color: red; text-align: center;'>Database connection failed: " . $conn->connect_error . "</p>";
                exit();
            }

            // Fetch job postings
            $sql = "SELECT * FROM job_postings ORDER BY created_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Check if user has already applied
                    $check_sql = "SELECT id FROM job_applications 
                                WHERE job_id = ? AND student_id = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("ii", $row['id'], $_SESSION['user_id']);
                    $check_stmt->execute();
                    $already_applied = $check_stmt->get_result()->num_rows > 0;
                    ?>
                    
                    <div class="job-posting">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <div class="job-meta">
                            <div class="meta-item">
                                <span>💰 $<?php echo number_format($row['salary'], 2); ?></span>
                            </div>
                            <div class="meta-item">
                                <span>📍 <?php echo htmlspecialchars($row['location']); ?></span>
                            </div>
                        </div>
                        <div class="job-details">
                            <p><strong>Description:</strong><br>
                            <?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                            
                            <p><strong>Requirements:</strong><br>
                            <?php echo nl2br(htmlspecialchars($row['requirements'])); ?></p>
                        </div>
                        
                        <?php if (!$already_applied) { ?>
                            <form class="application-form" action="apply_job.php" method="POST">
                                <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <textarea name="application_message" 
                                        placeholder="Write your application message here... Include why you're interested in this position and what makes you a good fit."
                                        required></textarea>
                                <button type="submit" class="apply-button">Apply Now</button>
                            </form>
                        <?php } else { ?>
                            <p style="color: #28a745;">✓ You have already applied for this position</p>
                        <?php } ?>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='no-jobs'>No job postings available at this time.</div>";
            }

            $conn->close();
            ?>
        </div>
    </div>

    <script>
        document.getElementById('searchJobs').addEventListener('input', function(e) {
            const searchText = e.target.value.toLowerCase();
            const jobPostings = document.querySelectorAll('.job-posting');
            
            jobPostings.forEach(posting => {
                const title = posting.querySelector('h3').textContent.toLowerCase();
                const description = posting.querySelector('.job-details').textContent.toLowerCase();
                
                if (title.includes(searchText) || description.includes(searchText)) {
                    posting.style.display = 'block';
                } else {
                    posting.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>