<?php
// Start the session
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$password = ""; // Default password for XAMPP is empty
$dbname = "fbla";

// Establish a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle logout if requested
if (isset($_POST['logout'])) {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie if it exists
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header("Location: loginpage.php");
    exit();
}

// Retrieve user data from session
$isLoggedIn = isset($_SESSION['user_id']); // Check if user is logged in by checking user_id
$userType = $_SESSION['user_type'] ?? null; // Retrieve user type from session

// Check if the user is logged in
if (!$isLoggedIn) {
    // Redirect to login page if the user is not logged in
    header("Location: loginpage.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="navbar-responsive.css">
    <style>
        .settings-container {
            max-width: 800px;
            margin: 100px auto;
            padding: 20px;
        }

        .settings-section {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .settings-section h2 {
            margin-bottom: 20px;
            color: #f57f17;
        }

        .settings-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .logout-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            width: fit-content;
            border: none;
            cursor: pointer;
        }

        .logout-button:hover {
            background-color: #c82333;
        }

        @media screen and (max-width: 768px) {
            .settings-container {
                margin: 80px 20px;
            }
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

    <div class="settings-container">
        <h1>Settings</h1>
        
        <div class="settings-section">
            <h2>Account Settings</h2>
            
            <div class="settings-options">
                <form method="POST" action="">
                    <button type="submit" name="logout" class="logout-button">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>