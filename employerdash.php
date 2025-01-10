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
        font-family: Arial;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }

        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }
        label {
            font-weight: bold;
        }
        input[type="text"], 
        input[type="number"], 
        textarea {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            box-sizing: border-box;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="navbar">
      <!-- Logo -->
      <a href="index.php">
          <img
              src="./media/logo.png"
              alt="Logo"
              class="logo"
              height="200px"
              width="auto"
          />
      </a>

      <!-- Navigation Links -->
      <div class="nav-links">
          <a href="aboutUs.php">About Us</a>
          <a href="resources.php">Resources</a>

          <!-- Display Login Link if Not Logged In -->
          <?php if (!$isLoggedIn): ?>
              <a href="./loginpage.php">Login</a>
          <?php endif; ?>

          <!-- Display Links for Logged-In Users -->
          <?php if ($isLoggedIn): ?>
              <!-- Show Student Dashboard if user is a student -->
              <?php if ($_SESSION['user_type'] === 'student'): ?>
                  <a href="studentdash.php">Student Dashboard</a>
              <?php endif; ?>

              <!-- Show Employer Dashboard if user is an employer -->
              <?php if ($_SESSION['user_type'] === 'employer'): ?>
                  <a href="employerdash.php">Employer Dashboard</a>
                  <a href="applicationsrecieved.php">Skibidi</a>
              <?php endif; ?>

              <!-- Profile/Settings Link -->
              <a href="settings.php" class="profile-link">
                  <img
                      src="./media/socialimage2.jpg"
                      alt="Profile"
                      class="profile-pic"
                  />
              </a>
          <?php endif; ?>
      </div>
    </div>

    <br>
    <br>
    <br>
    <br>

    <h1>Create a Job Posting</h1>
    
    <form action="create_job_posting.php" method="POST">
        <div>
            <label for="title">Job Title:</label>
            <input type="text" id="title" name="title" required 
                   maxlength="100" placeholder="e.g., Senior Software Engineer">
        </div>

        <div>
            <label for="description">Job Description:</label>
            <textarea id="description" name="description" rows="5" required
                      placeholder="Detailed description of the role and responsibilities"></textarea>
        </div>

        <div>
            <label for="requirements">Requirements:</label>
            <textarea id="requirements" name="requirements" rows="4" required
                      placeholder="List the required skills, experience, and qualifications"></textarea>
        </div>

        <div>
            <label for="salary">Salary (Annual):</label>
            <input type="number" id="salary" name="salary" step="0.01" required
                   min="0" placeholder="Enter annual salary">
        </div>

        <div>
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required
                   maxlength="255" placeholder="e.g., New York, NY">
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
            <label for="application_deadline">Application Deadline:</label>
            <input type="date" id="application_deadline" name="application_deadline" required>
        </div>

        <!-- Hidden field for company name - will be auto-filled by PHP -->
        <input type="hidden" id="company_name" name="company" 
               value="<?php echo htmlspecialchars($authorized_company); ?>">

        <button type="submit">Create Job Posting</button>
    </form>

    <script>
        // Set minimum date for application deadline to today
        const dateInput = document.getElementById('application_deadline');
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
    </script>
</body>
</html>
