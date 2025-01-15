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

      /* Main container styling */
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

      /* Styling for Resume Tips section */
      .resume-tips {
        margin-bottom: 30px;
      }

      .resume-tips > h2 {
        font-size: 28px;
        color: #2c3e50;
        margin-bottom: 15px;
      }

      .resume-tips p {
        font-size: 18px;
        line-height: 1.6;
        color: #4a4a4a;
      }

      .resume-tips ul {
        list-style-type: disc;
        margin-left: 20px;
      }

      .resume-tips a {
        font-size: 18px;
        font-weight: bold;
        color: #f57f17;
        text-decoration: none;
        margin-top: 10px;
        display: inline-block;
        transition: color 0.3s ease;
      }

      .resume-tips a:hover {
        color: #2c3e50;
      }

      /* Styling for Job Resources section */
      .job-resources {
        margin-bottom: 30px;
      }

      .job-resources > h2 {
        font-size: 28px;
        color: #2c3e50;
        margin-bottom: 15px;
      }

      .job-resources p {
        font-size: 18px;
        line-height: 1.6;
        color: #4a4a4a;
      }

      .job-resources ul {
        list-style-type: none;
        padding-left: 0;
      }

      .job-resources li {
        font-size: 18px;
      }

      .job-resources a {
        font-weight: bold;
        color: #f57f17;
        text-decoration: none;
      }

      .job-resources a:hover {
        color: #2c3e50;
      }

      /* Styling for Interview Prep section */
      .interview-prep {
        margin-bottom: 30px;
      }

      .interview-prep > h2 {
        font-size: 28px;
        color: #2c3e50;
        margin-bottom: 15px;
      }

      .interview-prep p {
        font-size: 18px;
        line-height: 1.6;
        color: #4a4a4a;
      }

      .interview-prep ul {
        list-style-type: disc;
        margin-left: 20px;
      }

      .interview-prep a {
        font-size: 18px;
        font-weight: bold;
        color: #f57f17;
        text-decoration: none;
        margin-top: 10px;
        display: inline-block;
        transition: color 0.3s ease;
      }

      .interview-prep a:hover {
        color: #2c3e50;
      }

      /* Styling for Student Success Stories section */
      .student-stories {
        margin-bottom: 30px;
      }

      .student-stories > h2 {
        font-size: 28px;
        color: #2c3e50;
        margin-bottom: 15px;
      }

      .student-stories p {
        font-size: 18px;
        line-height: 1.6;
        color: #4a4a4a;
      }

      .student-stories ul {
        list-style-type: none;
        padding-left: 0;
      }

      .student-stories li {
        font-size: 18px;
      }

      .student-stories a {
        font-weight: bold;
        color: #f57f17;
        text-decoration: none;
      }

      .student-stories a:hover {
        color: #2c3e50;
      }

      /* Styling for Upcoming Career Events section */
      .career-events {
        margin-bottom: 30px;
      }

      .career-events > h2 {
        font-size: 28px;
        color: #2c3e50;
        margin-bottom: 15px;
      }

      .career-events p {
        font-size: 18px;
        line-height: 1.6;
        color: #4a4a4a;
      }

      .career-events ul {
        list-style-type: none;
        padding-left: 0;
      }

      .career-events li {
        font-size: 18px;
      }

      .career-events a {
        font-size: 18px;
        font-weight: bold;
        color: #f57f17;
        text-decoration: none;
      }

      .career-events a:hover {
        color: #2c3e50;
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

        <!-- Additional Section: Resume Building Tips -->
        <div class="resume-tips">
          <h2>Resume Building Tips</h2>
          <p>
            <span>Building a strong resume is crucial for landing your dream job or internship. Here are some tips to get started:</span>
          </p>
          <ul>
            <li>Keep it concise – ideally 1 page long.</li>
            <li>Highlight your key skills and accomplishments.</li>
            <li>Use action verbs like "led," "developed," and "managed."</li>
            <li>Make sure to proofread for spelling and grammar mistakes.</li>
          </ul>
          <a href=""><span>Download Resume Template</span></a>
        </div>

        <!-- Additional Section: Job Search Resources -->
        <div class="job-resources">
          <h2>Job Search Resources</h2>
          <p>
            <span>Looking for your next opportunity? Here are some websites to help you with your job search:</span>
          </p>
          <ul>
            <li><a href="https://www.indeed.com">Indeed</a> – Popular job search engine for all industries.</li>
            <li><a href="https://www.linkedin.com/jobs">LinkedIn</a> – Great for networking and job listings.</li>
            <li><a href="https://www.glassdoor.com">Glassdoor</a> – Find jobs, company reviews, and salary information.</li>
          </ul>
        </div>

        <!-- Additional Section: Interview Preparation -->
        <div class="interview-prep">
          <h2>Interview Preparation</h2>
          <p>
            <span>Preparing for interviews is key to making a great first impression. Here are some tips:</span>
          </p>
          <ul>
            <li>Research the company and position beforehand.</li>
            <li>Practice answering common interview questions.</li>
            <li>Dress appropriately and be on time.</li>
            <li>Ask questions to show your interest in the role and company.</li>
          </ul>
          <a href=""><span>Interview Preparation Resources</span></a>
        </div>

        <!-- Additional Section: Student Success Stories -->
        <div class="student-stories">
          <h2>Student Success Stories</h2>
          <p>
            <span>Check out some inspiring success stories from students who have landed internships and full-time positions!</span>
          </p>
          <ul>
            <li><a href="">John Doe – Software Engineering Internship at XYZ Tech</a></li>
            <li><a href="">Jane Smith – Marketing Internship at ABC Corp</a></li>
            <li><a href="">Emily Johnson – Business Development at DEF Inc.</a></li>
          </ul>
        </div>

        <!-- Additional Section: Upcoming Career Events -->
        <div class="career-events">
          <h2>Upcoming Career Events</h2>
          <p>
            <span>Stay updated on upcoming events to network and learn from professionals!</span>
          </p>
          <ul>
            <li><span>Career Fair – February 20, 2025, 10:00 AM – 2:00 PM</span></li>
            <li><span>Resume Workshop – March 5, 2025, 3:00 PM – 5:00 PM</span></li>
            <li><span>Interview Skills Bootcamp – March 15, 2025, 1:00 PM – 4:00 PM</span></li>
          </ul>
          <a href=""><span>RSVP for Career Events</span></a>
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
