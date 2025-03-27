<?php
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$password = "mysql"; // Default password for XAMPP is empty
$dbname = "fbla";

// Establish a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']); // Check if user is logged in by checking user_id
$userType = $_SESSION['user_type'] ?? null; // Check user type from session

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error messages array
$errors = [];

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required form data is set
    if (isset($_POST['fname'], $_POST['lname'], $_POST['email'], $_POST['password'], $_POST['user_type'])) {
        // Get form data
        $first_name = $conn->real_escape_string($_POST['fname']);
        $last_name = $conn->real_escape_string($_POST['lname']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        $user_type = $conn->real_escape_string($_POST['user_type']);

        // Check if passwords match
        if (!isset($_POST['repeat-password']) || $_POST['password'] !== $_POST['repeat-password']) {
            $errors[] = "Passwords do not match.";
        }

        // Check if the email already exists in either the students or employers table
        $check_email_sql = "SELECT id FROM students WHERE email = ? UNION SELECT id FROM employers WHERE email = ?";
        $check_email_stmt = $conn->prepare($check_email_sql);
        $check_email_stmt->bind_param("ss", $email, $email);
        $check_email_stmt->execute();
        $check_email_stmt->store_result();

        if ($check_email_stmt->num_rows > 0) {
            $errors[] = "The email address is already taken. Please use a different email.";
        }

        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Handle based on user type
        if ($user_type === "student") {
            // Insert into Students table
            $sql = "INSERT INTO students (fname, lname, email, pass, user_type) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $user_type);
        } elseif ($user_type === "employer") {
            // Check if company_name is set for employers
            if (isset($_POST['company_name'])) {
                $company_name = $conn->real_escape_string($_POST['company_name']);

                // Insert into Employers table
                $sql = "INSERT INTO employers (fname, lname, email, pass, company_name, user_type) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssss", $first_name, $last_name, $email, $hashed_password, $company_name, $user_type);
            } else {
                $errors[] = "Company name is required for employer registration.";
            }
        } else {
            $errors[] = "Invalid user type specified.";
        }

        // Execute the query if there are no errors
        if (empty($errors) && $stmt->execute()) {
            // Redirect to success page or login page
            header("Location: loginpage.php");
            exit();
        }
        // Close the statement
        $stmt->close();
    } else {
        $errors[] = "Required form data missing.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Signup</title>
    <link rel="shortcut icon" href="./media/favicon.ico" type="image/x-icon" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
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
            background-color: #f0f0f0;
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

        /* Background Section */
        /* Background Section */
        .background {
            flex-grow: 1; /* Takes up space between nav and footer */
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url(https://educationsnapshots.com/wp-content/uploads/sites/4/2020/08/western-maricopa-education-center-northwest-campus-4.jpg);
            background-size: cover;
            background-position: center;
            position: relative;
        }

        /* Signup Container */
        .signup-container {
            background-color: white;
            width: 100%;
            max-width: 500px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
            margin: 20vh auto; /* Centered on the background */
        }

        .signup-container h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        /* Form Styles */
        .signup-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }

        .form-group input {
            padding: 10px;
            border: 1px solid #dcdcdc;
            border-radius: 5px;
            font-size: 16px;
            color: #333;
        }

        .form-group input:focus {
            border-color: #f57f17;
            outline: none;
        }

        /* Signup Button */
        .signup-btn {
            padding: 10px 20px;
            background-color: #f57f17;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .signup-btn:hover {
            background-color: #e65100;
        }

        /* Bottom Links */
        .bottom-links {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .bottom-links a {
            color: #f57f17;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .bottom-links a:hover {
            color: #2c3e50;
        }

        /* Footer */
        .footer {
            background-color: #1a2b4a;
            padding: 15px;
            text-align: center;
            color: white;
            font-family: Arial, sans-serif;
            width: 100%;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .contact-info p {
            margin: 5px 0;
            font-size: 16px;
        }

        .social-media-icons {
            margin: 20px 0;
        }

        .social-media-icons a {
            margin: 0 10px;
            display: inline-block;
        }

        .social-media-icons img {
            width: 24px;
            height: 24px;
        }

        .footer-note p {
            font-size: 14px;
            margin-top: 20px;
        }

        /* Tablet and Mobile Responsiveness */
        @media screen and (max-width: 768px) {
            /* Signup container adjustments */
            .signup-container {
                width: 90%;
                margin: 120px auto 40px; /* Increased top margin to account for navbar */
                padding: 20px;
            }

            .signup-container h1 {
                font-size: 24px;
            }

            /* Form adjustments */
            .form-group input {
                font-size: 14px;
                padding: 8px;
            }

            /* Bottom links adjustments */
            .bottom-links {
                flex-direction: column;
                gap: 10px;
                align-items: center;
                text-align: center;
            }

            /* Footer adjustments */
            .footer {
                padding: 10px;
            }

            .contact-info p {
                font-size: 14px;
            }

            .social-media-icons {
                margin: 15px 0;
            }

            .footer-note p {
                font-size: 12px;
            }
        }

        /* Small Mobile Devices */
        @media screen and (max-width: 480px) {
            /* Signup container adjustments */
            .signup-container {
                width: 95%;
                margin: 100px auto 20px;
                padding: 15px;
            }

            .signup-container h1 {
                font-size: 20px;
            }

            /* Form adjustments */
            .form-group label {
                font-size: 14px;
            }

            .form-group input {
                font-size: 13px;
                padding: 6px;
            }

            .signup-btn {
                padding: 8px 16px;
                font-size: 14px;
            }

            /* Footer adjustments */
            .social-media-icons a {
                margin: 0 5px;
            }

            .social-media-icons img {
                width: 20px;
                height: 20px;
            }
        }

        /* Additional improvements */
        @media screen and (max-width: 768px) {
            /* Background section adjustment */
            .background {
                padding: 20px;
            }

            /* Improve touch targets */
            button,
            .signup-btn {
                min-height: 44px;
                min-width: 44px;
            }
        }

        .error-msg {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }

    </style>
</head>
<body>
    <div class="navbar">    
      <a href="./index.php">
          <img src="./media/logo.png" alt="Logo" class="logo" height="200px" width="auto" />
      </a>
      <div class="nav-links">
          <a href="./aboutUs.php">About Us</a>
          <a href="./resources.php">Resources</a>
          <?php if ($isLoggedIn): ?>
              <?php if ($userType === 'student'): ?>
                  <a href="studentdash.php">Student Dashboard</a>
              <?php endif; ?>
              <?php if ($userType === 'employer'): ?>
                  <a href="./employerdash.php">Job Postings</a>
                  <a href="./applicationsrecieved.php">View Applications</a>
              <?php endif; ?>
              <?php if ($userType === 'admin'): ?>
                  <a href="./admindash.php">Admin Dashboard</a>
              <?php endif; ?>
              <a href="./settings.php" class="profile-link">
                  <img src="<?php 
                      $table = ($userType === 'student') ? 'students' : 
                              (($userType === 'employer') ? 'employers' : 'admins');
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
              <a href="./loginpage.php">Login</a>
          <?php endif; ?>
      </div>
    </div>

    <!-- Background Section -->
    <div class="background">
        <div class="signup-container">
            <h1>Student Sign Up</h1>
            
            

            <!-- Updated form -->
            <form id="signup-form" class="signup-form" method="POST" action="" onsubmit="return validatePasswords()">
                <div class="name-row">
                    <div class="form-group">
                        <label for="first-name">First Name</label>
                        <input type="text" id="first-name" name="fname" placeholder="Enter your first name" required>
                    </div>
                    <div class="form-group">
                        <label for="last-name">Last Name</label>
                        <input type="text" id="last-name" name="lname" placeholder="Enter your last name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <small style="color: grey; font-size: 12px;">Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, and one number.</small>
                </div>
                <div class="form-group">
                    <label for="repeat-password">Repeat Password</label>
                    <input type="password" id="repeat-password" name="repeat-password" placeholder="Repeat your password" required>
                </div>
                <input type="hidden" name="user_type" value="student"> <!-- Hidden input for user type -->

                <!-- Display errors if any -->
                <?php if (!empty($errors)): ?>
                    <div class="error-messages" style="color: red; font-size: 14px; margin-bottom: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="signup-btn">Sign Up</button>
            </form>
            <div class="bottom-links">
                <a href="./loginpage.php" class="login">Login</a>
                <a href="./employersignup.php" class="employer-signup">Employer Signup</a>
            </div>
        </div>
    </div>    

    <footer class="footer">
      <div class="footer-content">
        <div class="contact-info">
          <p>5487 N. 99th Ave, Glendale, AZ 85305</p>
          <p>
            P: 623.738.0022 &nbsp;&nbsp;|&nbsp;&nbsp; F: 623.738.0025
            &nbsp;&nbsp;|&nbsp;&nbsp; info@west-mec.org
          </p>
        </div>
        <div class="social-media-icons">
          <a href="#" class="social-button"
            ><i class="fa-brands fa-facebook"></i
          ></a>
          <a href="#" class="social-button"
            ><i class="fa-brands fa-twitter"></i
          ></a>
          <a href="#" class="social-button"
            ><i class="fa-brands fa-instagram"></i
          ></a>
          <a href="#" class="social-button"
            ><i class="fa-brands fa-linkedin"></i
          ></a>
          <a href="#" class="social-button"
            ><i class="fa-brands fa-youtube"></i
          ></a>
        </div>
        <div class="footer-note">
          <p>
            West-MEC strives to provide web content that is accessible to all.
            If you are unable to access any content, please contact
            info@west-mec.org.
          </p>
        </div>
      </div>
    </footer>

    <script>
        function validatePasswords() {
            const password = document.getElementById('password').value;
            const repeatPassword = document.getElementById('repeat-password').value;
            const errorMessage = document.getElementById('error-message');

            if (password !== repeatPassword) {
                errorMessage.style.display = 'block'; // Show error message
                return false; // Prevent form submission
            } else {
                errorMessage.style.display = 'none'; // Hide error message
                return true; // Allow form submission
            }
        }
    </script>
</body>
</html>
