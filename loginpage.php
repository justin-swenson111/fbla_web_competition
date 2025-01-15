<?php
session_start();

$servername = "localhost";
$username = "root";
$password = ""; // Default password for XAMPP is empty
$dbname = "fbla";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "
    SELECT id, fname, lname, email, pass, 'student' AS user_type FROM students WHERE email = ? 
    UNION 
    SELECT id, fname, lname, email, pass, 'employer' AS user_type FROM employers WHERE email = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['pass'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['fname'] = $user['fname'];
            $_SESSION['user_type'] = $user['user_type'];

            if ($user['user_type'] === 'student') {
                header("Location: studentdash.php");
            } else {
                header("Location: employerdash.php");
            }
            exit();
        } else {
            $loginError = "Incorrect password!";
        }
    } else {
        $loginError = "No user found with this email.";
    }

    $stmt->close();
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['fname']);
$userType = $_SESSION['user_type'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
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
      .background {
        flex-grow: 1; /* Takes up space between nav and footer */
        display: flex;
        justify-content: center;
        align-items: center;
        background-image: url(https://www.dlrgroup.com/media/2021/06/30_14130_00_N16_weblg-2140x1427.jpg);
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
        justify-content: center;
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
        <a href="index.php">
            <img src="./media/logo.png" alt="Logo" class="logo" />
        </a>
        <div class="nav-links">
            <a href="aboutUs.php">About Us</a>
            <a href="resources.php">Resources</a>
            <?php if ($isLoggedIn): ?>
                <?php if ($userType === 'student'): ?>
                    <a href="studentdash.php">Student Dashboard</a>
                <?php endif; ?>
                <?php if ($userType === 'employer'): ?>
                    <a href="employerdash.php">Job Postings</a>
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

    <div class="background">
        <div class="signup-container">
            <h1>Login</h1>
            <form id="login-form" class="signup-form" action="" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required />
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required />
                </div>
                <button type="submit" class="signup-btn">Login</button>
                <?php if (isset($loginError)): ?>
                    <p class="error-msg"><?php echo $loginError; ?></p>
                <?php endif; ?>
            </form>

            <div class="bottom-links">
                <a href="studentsignup.php" class="student-signup">Sign Up</a>
            </div>
        </div>
    </div>

    <footer class="footer" id="footer">
        <div class="footer-content">
            <div class="contact-info">
                <p>5487 N. 99th Ave, Glendale, AZ 85305</p>
                <p>
                    P: 623.738.0022 &nbsp;&nbsp;|&nbsp;&nbsp; F: 623.738.0025
                    &nbsp;&nbsp;|&nbsp;&nbsp; info@west-mec.org
                </p>
            </div>
            <div class="social-media-icons">
                <a href="#" class="social-button"><i class="fa-brands fa-facebook"></i></a>
                <a href="#" class="social-button"><i class="fa-brands fa-twitter"></i></a>
                <a href="#" class="social-button"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" class="social-button"><i class="fa-brands fa-linkedin"></i></a>
                <a href="#" class="social-button"><i class="fa-brands fa-youtube"></i></a>
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
  </body>
</html>
