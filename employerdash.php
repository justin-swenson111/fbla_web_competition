<?php
session_start();

$servername = "localhost";
$username = "root";
$password = ""; // Default password for XAMPP is empty
$dbname = "fbla";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']); // Check if user is logged in by checking user_id
$userType = $_SESSION['user_type'] ?? null; // Check user type from session

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle job posting form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requiredFields = ['title', 'description', 'requirements', 'salary', 'location', 'job_type', 'deadline'];
    $missingFields = [];

    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Missing required fields: ' . implode(', ', $missingFields),
        ];
    } else {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $requirements = $_POST['requirements'];
        $salary = $_POST['salary'];
        $location = $_POST['location'];
        $jobType = $_POST['job_type'];
        $deadline = $_POST['deadline'];

        // Get employer's company information
        $employerId = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT company_name FROM employers WHERE id = ?");
        $stmt->bind_param("i", $employerId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Employer information not found.',
            ];
        } else {
            $companyInfo = $result->fetch_assoc();
            $companyName = $companyInfo['company_name'];
            $stmt->close();

            // Insert the job posting into the database
            $insertSql = "INSERT INTO job_postings (employer_id, company_name, title, description, requirements, salary, location, job_type, application_deadline)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("issssdsss", $employerId, $companyName, $title, $description, $requirements, $salary, $location, $jobType, $deadline);

            if ($stmt->execute()) {
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Job posted successfully!',
                ];
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Error: ' . $stmt->error,
                ];
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Job Posting</title>
    <link rel="stylesheet" href="navbar-responsive.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 4rem;
            background-color: rgba(44, 62, 80, 0.9);
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1000;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar .logo {
            height: 50px;
            width: auto;
            transition: all 0.3s ease;
        }

        /* Navigation Links */
        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            padding: 0.7rem 1.2rem;
            border-radius: 4px;
            position: relative;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        /* Hover and Active States */
        .nav-links a:hover,
        .nav-links a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: #f57f17;
            transform: translateY(-2px);
        }

        .nav-links a::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0;
            height: 3px;
            background-color: #f57f17;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        /* Profile Link and Picture */
        .profile-link {
            padding: 0.5rem !important;
            margin-left: 0.5rem;
            display: flex;
            align-items: center;
        }

        .profile-pic {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            transition: transform 0.3s ease;
            border: 2px solid transparent;
        }

        .profile-pic:hover {
            transform: scale(1.1);
            border-color: #f57f17;
        }

        /* Responsive Adjustments */
        @media screen and (max-width: 1024px) {
            .navbar {
                padding: 0.8rem 2rem;
            }
            
            .nav-links {
                gap: 1rem;
            }
            
            .nav-links a {
                padding: 0.6rem 1rem;
            }
        }

        @media screen and (max-width: 768px) {
            .navbar {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }

            .navbar .logo {
                height: 40px;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                width: 100%;
                gap: 0.5rem;
            }

            .nav-links a {
                font-size: 0.9rem;
                padding: 0.5rem 0.8rem;
                text-align: center;
            }

            .profile-link {
                margin: 0;
            }
        }

        @media screen and (max-width: 480px) {
            .navbar {
                padding: 0.8rem 0.5rem;
            }

            .navbar .logo {
                height: 35px;
            }

            .nav-links {
                gap: 0.3rem;
            }

            .nav-links a {
                font-size: 0.85rem;
                padding: 0.4rem 0.6rem;
            }
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 70px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            color: #fff;
            font-size: 14px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
        }
        .notification.success {
            background-color: #4CAF50;
        }
        .notification.error {
            background-color: #f44336;
        }

        /* Form Styles */
        h1 {
            text-align: center;
            margin: 2rem 0;
            font-size: 2rem;
            color: #333;
        }

        form {
            max-width: 700px;
            margin: 20px auto;
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        form div {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #555;
        }

        input[type="text"], 
        input[type="number"], 
        textarea, 
        select, 
        input[type="date"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            box-sizing: border-box;
            border: 2px solid #ddd;
            border-radius: 8px;
            transition: border 0.3s ease;
        }

        input[type="text"]:focus, 
        input[type="number"]:focus, 
        textarea:focus, 
        select:focus, 
        input[type="date"]:focus {
            border-color: #f57f17;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Responsive Styles */
        @media screen and (max-width: 768px) {
            .navbar {
                padding: 1rem 2rem;
            }
        }
    </style>
</head>
<body>

    <div class="navbar">    
        <a href="index.php">
            <img src="./media/logo.png" alt="Logo" class="logo" height="50px" width="auto" />
        </a>
        <div class="nav-links">
            <a href="aboutUs.php">About Us</a>
            <a href="resources.php">Resources</a>
            <?php if ($isLoggedIn): ?>
                <?php if ($userType === 'student'): ?>
                    <a href="studentdash.php">Student Dashboard</a>
                <?php endif; ?>
                <?php if ($userType === 'employer'): ?>
                    <a href="employerdash.php">Employer Dashboard</a>
                    <a href="applicationsrecieved.php">View Applications</a>
                <?php endif; ?>
                <a href="settings.php" class="profile-link">
                    <img src="<?php 
                        $table = ($userType === 'student') ? 'students' : 'employers';
                        $stmt = $conn->prepare("SELECT profile_picture FROM $table WHERE id = ?");
                        $stmt->bind_param("i", $_SESSION['user_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $profile = $result->fetch_assoc();
                        $stmt->close();
                        echo !empty($profile['profile_picture']) ? htmlspecialchars($profile['profile_picture']) : './media/default-image.png';
                    ?>" 
                    alt="Profile" 
                    class="profile-pic" />
                </a>
            <?php else: ?>
                <a href="loginpage.php">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <h1>Create a Job Posting</h1>

    <!-- Notification -->
    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification <?= $_SESSION['notification']['type'] ?>">
            <?= htmlspecialchars($_SESSION['notification']['message']) ?>
        </div>
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>

    <form action="" method="POST">
        <div>
            <label for="title">Job Title:</label>
            <input type="text" id="title" name="title" required maxlength="100" placeholder="e.g., Senior Software Engineer">
        </div>

        <div>
            <label for="description">Job Description:</label>
            <textarea id="description" name="description" rows="5" required placeholder="Detailed description of the role and responsibilities"></textarea>
        </div>

        <div>
            <label for="requirements">Requirements:</label>
            <textarea id="requirements" name="requirements" rows="4" required placeholder="List the required skills, experience, and qualifications"></textarea>
        </div>

        <div>
            <label for="salary">Salary (Annual):</label>
            <input type="number" id="salary" name="salary" step="0.01" required min="0" placeholder="Enter annual salary">
        </div>

        <div>
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required maxlength="255" placeholder="e.g., New York, NY">
        </div>

        <div>
            <label for="job_type">Job Type:</label>
            <select id="job_type" name="job_type" required>
                <option value="">Select Job Type</option>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Contract">Contract</option>
                <option value="Internship">Internship</option>
            </select>
        </div>

        <div>
            <label for="deadline">Application Deadline:</label>
            <input type="date" id="deadline" name="deadline" required>
        </div>

        <button type="submit">Post Job</button>
    </form>

    <script>
        // Show the notification
        const notification = document.querySelector('.notification');
        if (notification) {
            notification.style.display = 'block';

            // Fade out after 5 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 1000); // Wait for the fade-out transition
            }, 5000);
        }

        window.addEventListener('load', () => {
            const navbar = document.getElementById('navbar');
            const notification = document.querySelector('.notification');
            
            if (navbar && notification) {
                const navbarHeight = navbar.offsetHeight;
                notification.style.top = `${navbarHeight + 10}px`; // Add a margin of 10px below the navbar
            }
        });

    </script>

</body>
</html>
