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
    <title>About Us</title>
    <link rel="shortcut icon" href="./media/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="styles.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />

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

      .container {
        /* Remove max-width and margin constraints */
        padding: 80px 20px; /* Increase padding for breathing room */
        background-color: #ffffff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow around the container */
      }

      .section-title {
        font-size: 2.5em; /* Increase font size for title */
        font-weight: 900;
        color: #1a2b4a;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 20px;
      }

      .section-title span {
        color: #f57f17;
      }

      .content {
        display: flex;
        gap: 40px; /* Increase gap between sections */
        margin-top: 30px;
      }

      .left-section,
      .right-section {
        flex: 1;
      }

      .left-section .card,
      .right-section {
        background-color: #ffffff;
        border-radius: 10px;
        padding: 30px; /* Add more padding for a cleaner look */
        margin-bottom: 30px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1); /* Enhance shadow for depth */
      }

      .left-section .card h2 {
        font-size: 1.8em; /* Increase font size for section headings */
        font-weight: bold;
        margin-bottom: 15px;
        color: #1a2b4a;
      }

      .left-section .card img,
      .right-section img {
        width: 100%;
        border-radius: 10px;
        margin-top: 15px;
      }

      .boundary-link {
        display: inline-block;
        margin-top: 15px;
        color: #f57f17;
        font-weight: bold;
        text-decoration: none;
        font-size: 1.1em; /* Make link text slightly larger */
      }

      .right-section h2 {
        font-size: 1.5em; /* Increase font size for list titles */
        font-weight: bold;
        margin-bottom: 15px;
        color: #1a2b4a;
      }

      .district-list {
        list-style-type: none;
        margin-top: 15px;
      }

      .district-list li {
        padding: 12px 0;
        font-size: 1.2em; /* Increase font size for list items */
        border-bottom: 1px solid #ddd;
        color: #f57f17;
      }

      .district-list li:last-child {
        border-bottom: none;
      }

      p {
        line-height: 1.6;
      }

      .info-section {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      }

      .info-section h3 {
        font-size: 1.3em;
        color: #1a2b4a;
        margin-top: 20px;
        margin-bottom: 10px;
      }

      .info-section ul {
        margin-top: 10px;
        padding-left: 20px;
      }

      .info-section ul li {
        list-style-type: disc;
        margin-bottom: 5px;
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
      /* Tablet and smaller screens */
      @media (max-width: 768px) {
        /* Content Layout */
        .content {
          flex-direction: column;
        }

        .left-section,
        .right-section {
          flex: none;
          width: 100%;
          margin-bottom: 20px;
        }

        /* Header Styles */
        .heading-main {
          flex-direction: column;
          text-align: center;
        }

        .heading-main > h1 {
          font-size: 40px;
        }

        .section-title {
          font-size: 2em;
        }

        /* Card and List Styles */
        .left-section .card,
        .right-section .card {
          padding: 20px;
        }

        /* Image Handling */
        .hiring img {
          width: 80%;
          margin-top: 15px;
        }

        /* Main Content Area */
        .mainpart {
          margin-left: 20px;
          margin-right: 20px;
        }

        /* Social Media Section */
        .social-media-links {
          grid-template-columns: repeat(3, 1fr);
        }

        .social-media-button {
          padding: 12px;
          font-size: 20px;
        }

        /* Footer Styling */
        .footer-content {
          padding: 20px;
        }

        .social-media-icons a {
          margin: 0 5px;
        }

        .contact-info p,
        .footer-note p {
          font-size: 14px;
        }
      }

      /* Mobile screens */
      @media (max-width: 480px) {

        /* Headers */
        .heading-main > h1 {
          font-size: 32px;
        }

        .section-title {
          font-size: 1.5em;
        }

        /* Content Spacing */
        .content {
          gap: 20px;
        }

        /* Lists */
        .district-list li {
          font-size: 1.1em;
        }

        /* Social Media */
        .social-media-links {
          grid-template-columns: repeat(2, 1fr);
        }

        .social-media-button {
          font-size: 18px;
          padding: 10px;
        }

        /* Footer */
        .footer-note p {
          font-size: 12px;
        }
      }

      /* Styling for the search input
        #search {
        margin-left: auto;
        width: 100%;
        padding: 8px;
        border-radius: 5px;
        font-size: 1rem;
        border: 1px solid #ccc;
        }

        /* The container for the search results (the dropdown)
        .search-wrapper {
        position: absolute;
        top: 100%; /* Position below the search bar
        left: 0;
        right: 0;
        z-index: 10;
        display: none; /* Hidden by default
        flex-direction: column;
        gap: .25rem;
        max-height: 300px; /* Max height for the dropdown
        overflow-y: auto; /* Scrollable if there are too many results
        background-color: white;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Display the dropdown when there is input text
        .search-wrapper.active {
        display: flex; /* Show dropdown
        }

        /* Individual result cards in the dropdown
        .card {
        border: 1px solid black;
        background-color: white;
        padding: .5rem;
        font-size: 1rem;
        margin-bottom: .25rem; /* Spacing between cards
        }

        /* Header section of each result card 
        .card > .header {
        margin-bottom: .25rem;
        font-weight: bold;
        }

        /* Styling for the link in the card body
        .card .body > a {
        text-decoration: none;
        color: #000e8d;
        }

        /* Hide items that don't match the search query 
        .hide {
        display: none;
        }

        /* Phone cards grid layout for other sections (not used in search bar but included for completeness)
        .phone-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: .25rem;
        }
        */
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


    <div class="container">
      <h1 class="section-title"><span>▶</span> WHAT IS WEST-MEC?</h1>

      <div class="content">
        <!-- Left Section -->
        <div class="left-section">
          <div class="card">
            <h2>A FASTER WAY FORWARD</h2>
            <p>
              Western Maricopa Education Center (West-MEC) is a public school
              district that provides innovative Career and Technical Education
              (CTE) programs.
            </p>
            <p>
              At West-MEC, we affirm that each student houses unique potential,
              and our innovative programs aim to bring that to the surface. We
              prepare students for a bright future through industry-standard
              equipment, experienced instructors, and leadership opportunities.
            </p>
            <img
              src="https://media.licdn.com/dms/image/D4D12AQGPuzy53w69sQ/article-cover_image-shrink_720_1280/0/1696006231317?e=2147483647&v=beta&t=FqBaJGPq6zZvMr6d5aCpq4a0sT7abAkakBE9v3gWtxY"
              alt="West-MEC Student"
            />
          </div>

          <div class="card">
            <h2>WHO WE SERVE</h2>
            <p>
              More than 37,000 students from 48 high schools in the northern and
              western cities of the Phoenix Metropolitan area are enrolled in
              West-MEC funded programs.
            </p>
            <p>
              Our West-MEC boundaries span 3,876 square miles, serving students
              at multiple locations. If you live within the following
              boundaries, you can enroll in West-MEC CTE programs.
            </p>
            <img
              src="https://educationsnapshots.com/wp-content/uploads/sites/4/2020/08/western-maricopa-education-center-northwest-campus-4.jpg"
              alt="West-MEC Campus"
            />
          </div>

          <!-- Additional Information Section -->
          <div class="info-section">
            <h3>MISSION</h3>
            <p>Preparing Students Today for Tomorrow’s Careers</p>

            <h3>CORE LEARNING VALUES</h3>
            <ul>
              <li>All students can learn</li>
              <li>Teachers make the difference</li>
              <li>High expectations</li>
              <li>Success leads to success</li>
            </ul>

            <h3>DIVERSITY STATEMENT</h3>
            <p>
              West-MEC is committed to creating a diverse, welcoming, and
              supportive environment in which all students and staff thrive and
              feel empowered to contribute to their community.
            </p>
          </div>
        </div>

        <!-- Right Section -->
        <div class="right-section">
          <p>
            <span style="font-weight: bold; font-size: 16px"
              >West-MEC partners with 15 public school districts and 2 charter
              districts to enhance a variety of CTE programs. These CTE programs
              are elective courses offered to students at their designated high
              school campuses. The following high schools offer West-MEC CTE
              Satellite Programs. Check with your counselor for more information
              about these programs.</span
            >
          </p>
          <br />
          <h2>MEMBER SCHOOL HIGH SCHOOL DISTRICTS</h2>
          <ul class="district-list">
            <li>AGUA FRIA UNION</li>
            <li>BUCKEYE UNION</li>
            <li>DEER VALLEY UNIFIED</li>
            <li>DYSART UNIFIED</li>
            <li>GILA BEND UNIFIED</li>
            <li>GLENDALE UNION</li>
            <li>NADABURG UNIFIED</li>
            <li>PARADISE VALLEY UNIFIED</li>
            <li>PEORIA UNIFIED</li>
            <li>SADDLE MOUNTAIN UNIFIED</li>
            <li>TOLLESON UNION</li>
            <li>WICKENBURG UNIFIED</li>
          </ul>

          <h2>MEMBER SCHOOL ELEMENTARY DISTRICTS</h2>
          <ul class="district-list">
            <li>CARTWRIGHT ELEMENTARY</li>
            <li>LITTLETON ELEMENTARY</li>
            <li>PENDERGAST ELEMENTARY</li>
          </ul>

          <h2>PARTNER CHARTER HIGH SCHOOL DISTRICTS</h2>
          <ul class="district-list">
            <li>PARADISE HONORS</li>
            <li>RIDGELINE ACADEMY</li>
          </ul>
          <br />
          <br />
          <br />
          <iframe
            src="https://viewer.mapme.com/e15b8efa-34b8-491a-96b1-76a271ff6c0f/location/f2e27491-ce83-48c3-b961-18d1b876b427"
            width="100%"
            height="590"
            frameborder="0"
            allowfullscreen
          >
          </iframe>
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
