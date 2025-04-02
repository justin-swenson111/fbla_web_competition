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

      /* Global styles and color palette */
      :root {
        --primary-blue: #1a2b4a;
        --primary-orange: #f57f17;
        --secondary-orange: #ffb74d;
        --text-dark: #2c3e50;
        --text-body: #4a4a4a;
        --bg-light: #f9f9f9;
        --white: #ffffff;
      }

      /* Main container styling */
      .mainpart {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 50px;
        background-color: var(--white);
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        margin-top: 30px;
        margin-bottom: 30px;
      }

      /* Header styling */
      .heading-main {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 3px solid var(--primary-orange);
      }

      .heading-main > h1 {
        font-weight: 800;
        font-size: 48px;
        color: var(--primary-blue);
        margin-left: 20px;
      }

      .heading-main > img {
        width: auto;
        height: 70px;
        border-radius: 8px;
      }

      /* Main content layout */
      .mainbody {
        display: grid;
        grid-template-columns: 1fr;
        gap: 40px;
      }

      @media (min-width: 992px) {
        .mainbody {
          grid-template-columns: 2fr 1fr;
        }
        
        .hiring, .resume-tips, .interview-prep {
          grid-column: 1;
        }
        
        .job-resources, .student-stories, .career-events {
          grid-column: 2;
        }
      }

      /* Section styles */
      .section-container {
        background-color: var(--white);
        border-radius: 8px;
        padding: 25px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
      }

      .section-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      }

      .hiring, .resume-tips, .job-resources, .interview-prep, .student-stories, .career-events {
        margin-bottom: 30px;
        position: relative;
        border-left: 4px solid var(--primary-orange);
        padding-left: 20px;
      }

      /* Section headers */
      .hiring > h2, .resume-tips > h2, .job-resources > h2, 
      .interview-prep > h2, .student-stories > h2, .career-events > h2 {
        font-size: 26px;
        color: var(--primary-blue);
        margin-bottom: 15px;
        position: relative;
        padding-bottom: 10px;
      }

      .hiring > h2::after, .resume-tips > h2::after, .job-resources > h2::after, 
      .interview-prep > h2::after, .student-stories > h2::after, .career-events > h2::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 3px;
        background-color: var(--primary-orange);
      }

      /* Paragraph styling */
      .hiring p, .resume-tips p, .job-resources p, 
      .interview-prep p, .student-stories p, .career-events p {
        font-size: 16px;
        line-height: 1.6;
        color: var(--text-body);
        margin-bottom: 15px;
      }

      /* Image styling */
      .hiring img {
        width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        margin-top: 20px;
        transition: transform 0.3s ease;
      }

      .hiring img:hover {
        transform: scale(1.02);
      }

      /* List styling */
      ul {
        list-style-position: inside;
        margin-bottom: 20px;
      }

      .resume-tips ul, .interview-prep ul {
        list-style-type: disc;
        padding-left: 20px;
      }

      .job-resources ul, .student-stories ul, .career-events ul {
        list-style-type: none;
        padding-left: 0;
      }

      li {
        font-size: 16px;
        line-height: 1.8;
        margin-bottom: 8px;
        position: relative;
      }

      .job-resources li, .student-stories li, .career-events li {
        padding-left: 20px;
      }

      .job-resources li::before, .student-stories li::before, .career-events li::before {
        content: '→';
        color: var(--primary-orange);
        position: absolute;
        left: 0;
      }

      /* Link styling */
      a {
        font-weight: bold;
        color: var(--primary-orange);
        text-decoration: none;
        transition: color 0.3s ease, transform 0.3s ease;
        display: inline-block;
      }

      a:hover {
        color: var(--primary-blue);
        transform: translateX(3px);
      }

      a[style="display: block"] {
        margin-bottom: 10px;
      }

      /* CTA buttons */
      .cta-button {
        display: inline-block;
        padding: 12px 24px;
        background-color: var(--primary-orange);
        color: white;
        font-weight: bold;
        border-radius: 6px;
        margin-top: 15px;
        transition: background-color 0.3s ease, transform 0.3s ease;
      }

      .cta-button:hover {
        background-color: var(--primary-blue);
        transform: translateY(-3px);
        color: white;
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

      /* Responsive styling */
      @media (max-width: 992px) {
        .mainbody {
          grid-template-columns: 1fr;
        }
        
        .hiring, .resume-tips, .interview-prep, .job-resources, .student-stories, .career-events {
          grid-column: 1;
        }
      }

      @media (max-width: 768px) {
        .mainpart {
          padding: 30px 25px;
          margin: 20px 15px;
        }
        
        .heading-main {
          flex-direction: column;
          text-align: center;
        }
        
        .heading-main > h1 {
          font-size: 36px;
          margin-left: 0;
          margin-top: 15px;
        }
        
        .hiring img {
          width: 100%;
        }
      }

      @media (max-width: 576px) {
        .mainpart {
          padding: 25px 20px;
          margin: 15px 10px;
        }
        
        .heading-main > h1 {
          font-size: 32px;
        }
        
        .heading-main > img {
          height: 60px;
        }
        
        .hiring > h2, .resume-tips > h2, .job-resources > h2, 
        .interview-prep > h2, .student-stories > h2, .career-events > h2 {
          font-size: 22px;
        }
        
        .contact-info p {
          font-size: 14px;
        }
        
        .social-media-icons a {
          width: 35px;
          height: 35px;
        }
      }

      /* Enhancement for featured content */
      .featured-content {
        background-color: rgba(245, 127, 23, 0.05);
        border-left: 4px solid var(--primary-orange);
        padding: 15px;
        margin: 20px 0;
        border-radius: 0 5px 5px 0;
      }

      /* Animation effects */
      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
      }

      .mainbody > div {
        animation: fadeIn 0.6s ease-out forwards;
      }

      .mainbody > div:nth-child(2) { animation-delay: 0.1s; }
      .mainbody > div:nth-child(3) { animation-delay: 0.2s; }
      .mainbody > div:nth-child(4) { animation-delay: 0.3s; }
      .mainbody > div:nth-child(5) { animation-delay: 0.4s; }
      .mainbody > div:nth-child(6) { animation-delay: 0.5s; }

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
                    <a href="scholarships.php">Scholarships</a>
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

    <div class="mainpart">
      <div class="heading-main">
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT1_d6gB7lxIuh2R_o_KGMOUNwxyVI7Z9X90A&s" alt="West-MEC Logo" />
        <h1>Career Services</h1>
      </div>

      <div class="mainbody">
        <div class="hiring section-container">
          <h2>Hiring & Internship Services</h2>
          <p>
            We understand you are busy and sometimes recruiting skilled
            candidates takes a back seat. West-MEC's Career Services
            Department can help. We offer a search and screening process
            tailored to your specific hiring needs at no cost to you.
          </p>
          <div class="featured-content">
            <p>
              Make sure that you are on the right path by contacting us today
              at <strong>623.738.0057</strong>
            </p>
          </div>
          <a href="" class="cta-button">Register as a Community Partner or Employer</a>
          <a href="" class="cta-button">West-MEC Job Board</a>
          <p>
            To Request an Education Verification, please fax 623.738.0028; be
            sure to include the student's authorization and year of
            completion.
          </p>
          <img
            src="https://resources.finalsite.net/images/f_auto,q_auto,t_image_size_3/v1703603997/westmecorg/whjkmab4mfnlvoby3nav/ma_halfpagepictall.png"
            alt="Career Services"
          />
        </div>

        <!-- Job Resources Section -->
        <div class="job-resources section-container">
          <h2>Job Search Resources</h2>
          <p>
            Looking for your next opportunity? Here are some websites to help you with your job search:
          </p>
          <ul>
            <li><a href="https://www.indeed.com">Indeed</a> – Popular job search engine for all industries.</li>
            <li><a href="https://www.linkedin.com/jobs">LinkedIn</a> – Great for networking and job listings.</li>
            <li><a href="https://www.glassdoor.com">Glassdoor</a> – Find jobs, company reviews, and salary information.</li>
          </ul>
        </div>

        <!-- Resume Tips Section -->
        <div class="resume-tips section-container">
          <h2>Resume Building Tips</h2>
          <p>
            Building a strong resume is crucial for landing your dream job or internship. Here are some tips to get started:
          </p>
          <ul>
            <li>Keep it concise – ideally 1 page long.</li>
            <li>Highlight your key skills and accomplishments.</li>
            <li>Use action verbs like "led," "developed," and "managed."</li>
            <li>Make sure to proofread for spelling and grammar mistakes.</li>
          </ul>
          <a href="" class="cta-button">Download Resume Template</a>
        </div>

        <!-- Student Stories Section -->
        <div class="student-stories section-container">
          <h2>Student Success Stories</h2>
          <p>
            Check out some inspiring success stories from students who have landed internships and full-time positions!
          </p>
          <ul>
            <li><a href="">John Doe – Software Engineering Internship at XYZ Tech</a></li>
            <li><a href="">Jane Smith – Marketing Internship at ABC Corp</a></li>
            <li><a href="">Emily Johnson – Business Development at DEF Inc.</a></li>
          </ul>
        </div>

        <!-- Interview Prep Section -->
        <div class="interview-prep section-container">
          <h2>Interview Preparation</h2>
          <p>
            Preparing for interviews is key to making a great first impression. Here are some tips:
          </p>
          <ul>
            <li>Research the company and position beforehand.</li>
            <li>Practice answering common interview questions.</li>
            <li>Dress appropriately and be on time.</li>
            <li>Ask questions to show your interest in the role and company.</li>
          </ul>
          <a href="" class="cta-button">Interview Preparation Resources</a>
        </div>

        <!-- Career Events Section -->
        <div class="career-events section-container">
          <h2>Upcoming Career Events</h2>
          <p>
            Stay updated on upcoming events to network and learn from professionals!
          </p>
          <ul>
            <li><span>Career Fair – February 20, 2025, 10:00 AM – 2:00 PM</span></li>
            <li><span>Resume Workshop – March 5, 2025, 3:00 PM – 5:00 PM</span></li>
            <li><span>Interview Skills Bootcamp – March 15, 2025, 1:00 PM – 4:00 PM</span></li>
          </ul>
          <a href="" class="cta-button">RSVP for Career Events</a>
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
