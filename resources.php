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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Resources</title>
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

      .mainpart {
        margin-right: 100px;
        margin-left: 100px;
      }

      /* Main */
      .heading-main {
        display: flex;
        align-items: center;
      }

      .heading-main > h1 {
        font-weight: 800;
        font-size: 60px;
      }

      .heading-main > img {
        width: auto;
        height: 70px;
      }

      /* Styling for the Hiring section */
      .hiring {
        margin-bottom: 30px;
      }

      .hiring > h2 {
        font-size: 28px; /* Increase the font size */
        color: #2c3e50; /* A professional dark blue color */
        margin-bottom: 15px;
      }

      .hiring p {
        font-size: 18px; /* Increase font size for readability */
        line-height: 1.6; /* Add better line spacing */
        color: #4a4a4a; /* A soft dark gray for better readability */
      }

      .hiring a {
        font-size: 18px; /* Make links consistent with the paragraph text */
        font-weight: bold; /* Highlight the links */
        color: #f57f17; /* Add an accent color for links */
        text-decoration: none;
        margin-top: 10px;
        display: inline-block;
        transition: color 0.3s ease;
      }

      .hiring a:hover {
        color: #2c3e50; /* Hover effect for links */
      }

      .hiring img {
        margin-top: 20px;
        width: 50%; /* Make the image responsive */
        height: auto;
        border-radius: 8px; /* Add rounded corners for a modern look */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add a subtle shadow */
      }

      /* Add spacing between the hiring section and aside */
      .mainbody {
        gap: 30px; /* Flexbox gap for spacing between elements */
      }

      /* Footer */
      .footer {
        background-color: #1a2b4a;
        padding: 30px;
        text-align: center;
        color: white;
        font-family: Arial, sans-serif;
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
      /* Mobile-Friendly Styling */
      @media (max-width: 768px) {
        .heading-main {
          flex-direction: column;
          text-align: center;
        }

        .heading-main > h1 {
          font-size: 40px;
        }

        .hiring img {
          width: 80%;
          margin-top: 15px;
        }

        .mainpart {
          margin-left: 20px;
          margin-right: 20px;
        }

        .footer-content {
          padding: 20px;
        }

        .social-media-icons a {
          margin: 0 5px;
        }
      }

      @media (max-width: 480px) {
        .heading-main > h1 {
          font-size: 32px;
        }

        .nav-links a {
          font-size: 14px;
          padding: 8px 10px;
        }

        .footer-note p {
          font-size: 12px;
        }
      }
    </style>
  </head>
  <body>
    <div class="navbar">    
      <a href="index.php">
          <img src="./media/logo.png" alt="Logo" class="logo" height="200px" width="auto" />
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

    <div class="mainpart">
      <div class="heading-main">
        <img
          src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT1_d6gB7lxIuh2R_o_KGMOUNwxyVI7Z9X90A&s"
        />
        <h1>Career Services</h1>
      </div>

      <br />
      <div class="mainbody">
        <div class="hiring">
          <h2>Hiring & Internship Services</h2>
          <p>
            <span
              >We understand you are busy and sometimes recruiting skilled
              candidates takes a back seat. West-MEC’s Career Services
              Department can help. We offer a search and screening process
              tailored to your specific hiring needs at no cost to you.</span
            >
          </p>
          <p>
            <span
              >Make sure that you are on the right path by contacting us today
              at 623.738.0057</span
            >
          </p>
          <a href="" style="display: block"
            ><span>Register as a Community Partner or Employer</span></a
          >
          <a href=""><span>West-MEC Job Board</span></a>
          <p>
            <span
              >To Request an Education Verification, please fax 623.738.0028; be
              sure to include the student's authorization and year of
              completion.</span
            >
          </p>
          <img
            src="https://resources.finalsite.net/images/f_auto,q_auto,t_image_size_3/v1703603997/westmecorg/whjkmab4mfnlvoby3nav/ma_halfpagepictall.png"
          />
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
  </body>
</html>
