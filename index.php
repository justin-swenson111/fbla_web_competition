<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "mysql"; // Default password for XAMPP is empty
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
    <title>West-MEC NECareers</title>
    <link rel="shortcut icon" href="./media/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="styles.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link rel="stylesheet" href="navbar-responsive.css" />

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

      /* Banner */
      .banner {
        position: relative;
        height: 750px;
        overflow: hidden;
        top: 0;
        z-index: 1;
      }

      .background-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 1;
      }

      .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(
          to right,
          rgba(245, 127, 23, 0.8),
          rgba(255, 183, 77, 0.8)
        );
        z-index: 2;
      }

      .banner-content {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        position: relative;
        z-index: 3;
        text-align: center;
      }

      .text-section {
        color: white;
        text-align: center;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
      }

      .text-section h1 {
        font-size: 120px;
        margin-bottom: 15px;
        font-family: Arial;
        font-weight: 900;
        letter-spacing: 2px;
        font-style: italic;
      }

      .text-section p {
        font-size: 40px;
        font-weight: 900;
        font-family: Arial;
        margin-top: 0;
        letter-spacing: 1px;
        text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.5);
        font-style: italic;
      }

      /* Bottom Banner */
      .bottom-bar {
        background-color: #0a314d;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
      }

      .bottom-bar-text {
        color: white;
        font-size: large;
        padding-right: 50px;
      }

      .bottom-bar button {
        background-color: transparent;
        border: 2px black solid;
        padding: 15px 30px;
        color: white;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        border-radius: 6px;
        transition: border 0.3s ease;
      }

      .bottom-bar button:hover {
        border: 2px rgba(245, 127, 23) solid;
      }

      /* Button Container */
      .button-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
        padding: 20px;
      }

      .program-button {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 300px;
        height: 200px;
        background-color: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
      }

      .program-button:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
      }

      .program-button i {
        font-size: 24px;
        color: #ff8c00;
        margin-bottom: 10px;
      }

      .program-button span {
        font-family: Arial, sans-serif;
        font-size: 20px;
        font-weight: 100;
        text-align: center;
        color: #333;
      }

      /*Map*/
      .visit-us-container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        margin: 50px;
      }

      .map-container {
        margin: 20px 0; /* Adds space between the heading and the map */
      }

      .map-container iframe {
        border: 5px solid black;
        border-radius: 10px; /* Rounds the corners of the border */
      }

      h2 {
        font-size: 48px;
        color: #1a2b4a; /* Customize the font color */
        font-weight: 900;
        font-style: italic;
      }

      /* Companies */
      .hire-container {
        display: flex;
        justify-content: space-between;
        align-items: flex-start; /* Keep alignment at the start for consistent vertical alignment */
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        gap: 100px; /* Adjusted gap for better balance */
      }

      .video-section {
        flex: 1;
        margin-right: 80px;
        text-align: center; /* Center-align the heading and video */
      }

      .video-heading {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 20px; /* Space between heading and video */
        color: #1a2b4a; /* Same color as the text headings */
      }

      .video-section video {
        width: 100%; /* Make the video responsive */
        max-width: 800px; /* Set a max width for the video */
        height: auto; /* Maintain aspect ratio */
        border: 5px solid black;
        border-radius: 10px;
      }

      .companies-container {
        flex: 1;
        font-family: Arial, sans-serif;
        text-align: center;
      }

      .hireheader {
        color: #1a2b4a;
        font-size: 60px;
        font-weight: 900;
        margin-bottom: 10px;
        font-style: italic;
      }

      .hirefooter {
        color: #333;
        font-size: 30px;
        font-weight: 900;
        margin-bottom: 30px;
      }

      .companies-list {
        display: flex;
        justify-content: space-around; /* Adjusted for better layout */
        gap: 50px; /* Reduced gap for better spacing */
        flex-wrap: wrap; /* Allow wrapping on smaller screens */
      }

      .column {
        text-align: center;
        flex: 1;
        min-width: 200px; /* Minimum width for columns to ensure they don't get too small */
      }

      .column p {
        color: #1a2b4a;
        margin: 10px 0; /* Reduced margin for better spacing */
        font-size: 20px;
        font-weight: 700;
        width: 100%; /* Ensuring the text takes the full width of its column */
      }

      /* Social Media Section */
      .west-mec-social-media-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        width: 100%;
        margin-top: 40px;
      }

      .orange-wave {
        color: white;
        background-color: #f57f17;
        font-size: 48px;
        font-weight: 900;
        font-style: italic;
        padding: 10px 0;
        width: 100%;
        margin: 0;
        box-sizing: border-box;
        text-align: center;
      }

      .social-media-links {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        width: 100%;
        position: relative;
      }

      .social-media-item {
        position: relative;
        width: 100%;
      }

      .social-media-item img {
        width: 100%;
        height: auto;
        display: block;
        object-fit: cover;
      }

      .social-media-button {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: orange;
        color: white;
        border-radius: 50%;
        padding: 15px;
        font-size: 24px;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 60px;
        height: 60px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s ease;
      }

      .social-media-button:hover {
        background-color: #1a2b4a;
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

      /* Keep your existing base styles ... */

      /* Tablet and Mobile Responsiveness */
      @media screen and (max-width: 768px) {
        /* 2. Banner adjustments */
        .banner {
          height: 500px;
        }

        .text-section h1 {
          font-size: 60px;
        }

        .text-section p {
          font-size: 24px;
        }

        .bottom-bar {
          flex-direction: column;
          height: auto;
          padding: 20px;
        }

        .bottom-bar-text {
          padding-right: 0;
          text-align: center;
          margin-bottom: 10px;
        }

        /* 3. Video section adjustments */
        .hire-container {
          flex-direction: column;
          gap: 40px;
        }

        .video-section {
          margin-right: 0;
          width: 100%;
        }

        .video-section video {
          width: 100%;
          height: auto;
        }

        /* 4. Social media adjustments */
        .social-media-links {
          flex-direction: column;
        }

        .orange-wave {
          font-size: 36px;
        }
      }

      /* Small Mobile Devices */
      @media screen and (max-width: 320px) {
        /* Banner text adjustments */
        .text-section h1 {
          font-size: 40px;
        }

        .text-section p {
          font-size: 18px;
        }

        /* Bottom bar adjustments */
        .bottom-bar button {
          padding: 10px 20px;
          font-size: 14px;
          width: 100%;
        }

        /* Video section adjustments */
        .video-heading {
          font-size: 24px;
        }

        .hireheader {
          font-size: 36px;
        }

        .hirefooter {
          font-size: 20px;
        }

        /* Social media adjustments */
        .social-media-links {
          grid-template-columns: 1fr; /* Single column */
        }

        .social-media-button {
          width: 40px;
          height: 40px;
          font-size: 18px;
        }

        .orange-wave {
          font-size: 28px;
        }

        /* Companies section */
        .companies-list {
          gap: 20px;
        }

        .column p {
          font-size: 16px;
        }
      }

      /* Additional improvements for smooth responsiveness */
      @media screen and (max-width: 768px) {
        /* Ensure all images and videos are responsive */
        img,
        video {
          max-width: 100%;
          height: auto;
        }

        /* Improve touch targets */
        button,
        .social-media-button {
          min-height: 44px;
          min-width: 44px;
        }

        /* Program buttons adjustment */
        .program-button {
          width: 100%;
          max-width: 280px;
        }

        /* Map container adjustment */
        .map-container iframe {
          width: 100% !important;
          max-width: 100%;
          height: 300px !important;
        }
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

    <div class="banner">
      <video autoplay muted loop class="background-video">
        <source
          src="./media/Northeast Campus - West-MEC.mp4"
          type="video/mp4"
        />
        Your browser does not support the video tag.
      </video>
      <div class="overlay"></div>
      <div class="banner-content">
        <div class="text-section">
          <h1>A FASTER WAY FORWARD</h1>
          <p>WELCOME TO NECareers</p>
          <p>The Official Northeast Campus Job Page</p>
        </div>
      </div>
    </div>

    <div class="bottom-bar">
      <p class="bottom-bar-text">Ready to see our orange impact in action?</p>
      <div class="bottom-bar-buttons">
        <button>Recruiter Virtual Visit</button>
        <button>On Campus Tour</button>
        <button>Request a Recruiter</button>
      </div>
    </div>

    <div class="button-container">
      <button class="program-button">
        <i class="far fa-calendar-alt"></i>
        <span>High School Program Calendars</span>
      </button>
      <button class="program-button">
        <i class="fas fa-user-friends"></i>
        <span>Programs</span>
      </button>
      <button
        class="program-button"
        onclick="window.location.href='https://focus.west-mec.edu/focus/Modules.php?modname=misc%2FPortal.php';"
      >
        <i class="fas fa-clipboard-check"></i>
        <span>Grades & Attendance</span>
      </button>
      <button class="program-button">
        <i class="fas fa-folder-open"></i>
        <span>Student & Parent Resources</span>
      </button>
      <button class="program-button">
        <i class="fas fa-chalkboard-teacher"></i>
        <span>Professional Development</span>
      </button>
    </div>

    <div class="visit-us-container">
      <h2>Come Visit Us</h2>
      <div class="map-container">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4694.724028867614!2d-112.09405498808286!3d33.69157710020937!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x872b6f81f1c0ca5d%3A0xf115943748fc5b2b!2sWest-MEC%20-%20Northeast%20Campus!5e0!3m2!1sen!2sus!4v1729366196907!5m2!1sen!2sus"
          width="1000"
          height="450"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
        >
        </iframe>
      </div>
    </div>

    <div class="hire-container">
      <div class="video-section">
        <h2 class="video-heading">See why people hire from West-MEC</h2>
        <video controls>
          <source
            src="./media/Why I Hire West-MEC - Adelante Healthcare.mp4"
            type="video/mp4"
          />
          Your browser does not support the video tag.
        </video>
        <video controls>
          <source
            src="./media/Why I Hire West-MEC - Nash Powersports.mp4"
            type="video/mp4"
          />
          Your browser does not support the video tag.
        </video>
        <video controls>
          <source
            src="./media/Why I Hire West-MEC - Coreslab Structures.mp4"
            type="video/mp4"
          />
          Your browser does not support the video tag.
        </video>
      </div>
      <div class="companies-container">
        <h1 class="hireheader">A FASTER WAY FORWARD</h1>
        <h2 class="hirefooter">Companies that have hired our students...</h2>
        <div class="companies-list">
          <div class="column">
            <p>APS/Palo Verde</p>
            <p>AZ Pet Vet</p>
            <p>Canyon State Electric</p>
            <p>Department of Public Safety</p>
            <p>Honeywell</p>
            <p>Ohana Salon</p>
            <p>Spooner Physical Therapy</p>
          </div>
          <div class="column">
            <p>Athletico</p>
            <p>Banner Health</p>
            <p>Corning Advanced Optics</p>
            <p>Envoy Air</p>
            <p>Humana</p>
            <p>Phoenix Children's Hospital</p>
            <p>Subaru</p>
          </div>
          <div class="column">
            <p>AerSale</p>
            <p>Caliber Collision</p>
            <p>CVS Pharmacy</p>
            <p>Ford</p>
            <p>Kearney Electric</p>
            <p>Rummel Construction</p>
          </div>
        </div>
      </div>
    </div>

    <div class="west-mec-social-media-section">
      <div class="orange-wave">
        <p>FOLLOW THE ORANGE WAVE</p>
      </div>
      <div class="social-media-links">
        <div class="social-media-item">
          <a href="#"><img src="./media/socialimage1.jpg" /></a>
          <div class="social-media-button">
            <i class="fa-brands fa-facebook"></i>
          </div>
        </div>
        <div class="social-media-item">
          <a href="#"><img src="./media/socialimage2.jpg" /></a>
          <div class="social-media-button">
            <i class="fa-brands fa-twitter"></i>
          </div>
        </div>
        <div class="social-media-item">
          <a href="#"><img src="./media/socialimage3.jpg" /></a>
          <div class="social-media-button">
            <i class="fa-brands fa-instagram"></i>
          </div>
        </div>
        <div class="social-media-item">
          <a href="#"><img src="./media/socialimage4.jpg" /></a>
          <div class="social-media-button">
            <i class="fa-brands fa-linkedin"></i>
          </div>
        </div>
        <div class="social-media-item">
          <a href="#"><img src="./media/socialimage5.jpg" /></a>
          <div class="social-media-button">
            <i class="fa-brands fa-youtube"></i>
          </div>
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
