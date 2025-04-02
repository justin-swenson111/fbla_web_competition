<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "mysql"; 
$dbname = "fbla";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check if user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userType = $_SESSION['user_type'] ?? null;

// Get student's first name
$stmt = $conn->prepare("SELECT fname FROM students WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['fname'] = $row['fname'];
}

// Handle AJAX request for applications
if (isset($_GET['get_applications'])) {
    header('Content-Type: application/json');
    
    // Pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    // Get total count for pagination
    $total_query = "SELECT COUNT(*) as count FROM job_applications WHERE student_id = ?";
    $total_stmt = $conn->prepare($total_query);
    $total_stmt->bind_param("i", $_SESSION['user_id']);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_records = $total_row['count'];
    $total_pages = ceil($total_records / $limit);
    
    // Get applications with pagination
    $apps_sql = "SELECT ja.*, jp.title, jp.company_name
                FROM job_applications ja 
                JOIN job_postings jp ON ja.job_posting_id = jp.id 
                WHERE ja.student_id = ? 
                ORDER BY ja.applied_at DESC
                LIMIT ? OFFSET ?";
    
    $apps_stmt = $conn->prepare($apps_sql);
    $apps_stmt->bind_param("iii", $_SESSION['user_id'], $limit, $offset);
    $apps_stmt->execute();
    $apps_result = $apps_stmt->get_result();
    
    $applications = [];
    while ($app = $apps_result->fetch_assoc()) {
        $applications[] = $app;
    }
    
    echo json_encode([
        'applications' => $applications,
        'total_pages' => $total_pages,
        'current_page' => $page
    ]);
    exit();
}

// Handle AJAX request for filtered jobs
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    // Pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    // Get filter parameters
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $location = isset($_GET['location']) ? $_GET['location'] : '';
    $salary_range = isset($_GET['salary']) ? $_GET['salary'] : '';
    $job_type = isset($_GET['job_type']) ? $_GET['job_type'] : '';  // Fixed parameter name
    $job_subject = isset($_GET['subject']) ? $_GET['subject'] : '';  // Fixed parameter name

    // First, we need to count the total number of matching records for pagination
    $count_query = "SELECT COUNT(*) as total FROM job_postings jp 
                  LEFT JOIN employers e ON jp.employer_id = e.id
                  WHERE jp.is_active = 1";
    
    $count_params = [];
    $count_types = "";

    if ($search) {
        $count_query .= " AND (jp.title LIKE ? OR jp.description LIKE ?)";
        $search_param = "%$search%";
        array_push($count_params, $search_param, $search_param);
        $count_types .= "ss";
    }

    if ($location) {
        $count_query .= " AND jp.location = ?";
        array_push($count_params, $location);
        $count_types .= "s";
    }

    if ($job_type) {
        $count_query .= " AND jp.job_type = ?";
        array_push($count_params, $job_type);
        $count_types .= "s";
    }

    if ($job_subject) {
        $count_query .= " AND jp.job_subject = ?";
        array_push($count_params, $job_subject);
        $count_types .= "s";
    }

    if ($salary_range) {
        $salary_parts = explode('-', $salary_range);
        if (count($salary_parts) == 2) {
            $count_query .= " AND jp.salary BETWEEN ? AND ?";
            array_push($count_params, (float)$salary_parts[0], (float)$salary_parts[1]);
            $count_types .= "dd";
        } elseif (strpos($salary_range, '+') !== false) {
            $count_query .= " AND jp.salary >= ?";
            array_push($count_params, (float)str_replace('+', '', $salary_range));
            $count_types .= "d";
        }
    }

    $count_stmt = $conn->prepare($count_query);
    if (!empty($count_params)) {
        $count_stmt->bind_param($count_types, ...$count_params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_records = $count_row['total'];
    $total_pages = ceil($total_records / $limit);

    // Build the main query with employer and subject information
    $query = "SELECT jp.*, e.fname AS employer_fname, e.lname AS employer_lname, 
          e.profile_picture, e.industry, e.company_location, e.company_description, 
          e.company_website, jp.job_subject
          FROM job_postings jp 
          LEFT JOIN employers e ON jp.employer_id = e.id
          WHERE jp.is_active = 1";
    
    $params = [];
    $types = "";

    if ($search) {
        $query .= " AND (jp.title LIKE ? OR jp.description LIKE ?)";
        $search_param = "%$search%";
        array_push($params, $search_param, $search_param);
        $types .= "ss";
    }

    if ($location) {
        $query .= " AND jp.location = ?";
        array_push($params, $location);
        $types .= "s";
    }

    if ($job_type) {
        $query .= " AND jp.job_type = ?";
        array_push($params, $job_type);
        $types .= "s";
    }

    if ($job_subject) {
        $query .= " AND jp.job_subject = ?";
        array_push($params, $job_subject);
        $types .= "s";
    }

    if ($salary_range) {
        $salary_parts = explode('-', $salary_range);
        if (count($salary_parts) == 2) {
            $query .= " AND jp.salary BETWEEN ? AND ?";
            array_push($params, (float)$salary_parts[0], (float)$salary_parts[1]);
            $types .= "dd";
        } elseif (strpos($salary_range, '+') !== false) {
            $query .= " AND jp.salary >= ?";
            array_push($params, (float)str_replace('+', '', $salary_range));
            $types .= "d";
        }
    }

    $query .= " ORDER BY jp.created_at DESC LIMIT ? OFFSET ?";
    array_push($params, $limit, $offset);
    $types .= "ii";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    $jobs = [];

    while ($row = $result->fetch_assoc()) {
        $check_sql = "SELECT id FROM job_applications WHERE job_posting_id = ? AND student_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $row['id'], $_SESSION['user_id']);
        $check_stmt->execute();
        $already_applied = $check_stmt->get_result()->num_rows > 0;
        
        $row['already_applied'] = $already_applied;
        $row['formatted_salary'] = number_format($row['salary'], 2);
        $row['formatted_deadline'] = date('M d, Y', strtotime($row['application_deadline']));
        
        // Include employer's information
        $row['employer_fname'] = $row['employer_fname'];
        $row['employer_lname'] = $row['employer_lname'];
        $row['profile_picture'] = $row['profile_picture'];
        $row['job_subject'] = $row['job_subject']; // Include subject in response
        
        $jobs[] = $row;
    }

    echo json_encode([
        'jobs' => $jobs,
        'total_pages' => $total_pages,
        'current_page' => $page
    ]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="shortcut icon" href="./media/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="navbar-responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .filters {
            margin: 20px 0;
            padding: 15px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-bar {
            flex: 1;
            min-width: 200px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-width: 150px;
        }       

        .job-posting {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .job-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .employer-profile {
            position: relative;
        }

        .employer-profile-pic {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid #ddd;
            transition: border-color 0.3s ease;
        }

        .employer-profile-pic:hover {
            border-color: #007bff;
        }

        .profile-image-container {
            position: relative;
        }

        .profile-hover-card {
            position: absolute;
            top: 100%;
            left: 0;
            width: 300px;
            max-width: 90vw;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 1rem;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .profile-image-container:hover .profile-hover-card {
            opacity: 1;
            visibility: visible;
        }

        .profile-hover-card h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }

        .profile-hover-card p {
            margin: 0.5rem 0;
            font-size: 0.9rem;
            color: #666;
        }

        .company-description {
            max-height: 100px;
            overflow-y: auto;
            margin: 0.5rem 0;
            padding: 0.5rem 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }

        .company-website {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.5rem 1rem;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .company-website:hover {
            background: #0056b3;
        }

        .job-title-section {
            flex: 1;
            min-width: 250px;
        }

        .job-title-section h3 {
            margin-top: 0;
            color: #007bff;
        }

        .job-details {
            margin: 15px 0;
        }

        .application-form {
            margin-top: 15px;
        }

        .application-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 10px;
        }

        .file-upload {
            margin: 10px 0;
        }

        .apply-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .apply-button:hover {
            background-color: #218838;
        }

        .apply-button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        .no-jobs {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .job-meta {
            display: flex;
            gap: 20px;
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #007bff;
            margin-bottom: 5px;
        }

        .pagination a.active {
            background-color: #007bff;
            color: white;
        }

        .my-applications {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .application-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            display: inline-block;
        }

        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }

        .status-accepted {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-reviewed {
            background-color: #cce5ff;
            color: #004085;
        }

        .loading {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }

        /* General Layout Consistency */
        .bookmarked-jobs {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .bookmarked-jobs h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        #bookmarked-jobs-list {
            margin-top: 20px;
        }

        /* Job Posting Cards Consistency */
        #bookmarked-jobs-list .job-posting {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        /* Hover effect for job cards */
        #bookmarked-jobs-list .job-posting:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Job Title Styling */
        #bookmarked-jobs-list h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        /* Actions Section Styling */
        #bookmarked-jobs-list .job-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* View Job Button Styling */
        #bookmarked-jobs-list .view-job-btn,
        #bookmarked-jobs-list .remove-bookmark-btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            border: none;
            transition: background-color 0.2s ease;
        }

        /* View Job Button Color */
        #bookmarked-jobs-list .view-job-btn {
            background-color: #4a89dc;
            color: white;
        }

        /* Hover effect for View Job Button */
        #bookmarked-jobs-list .view-job-btn:hover {
            background-color: #3b7ddb;
        }

        /* Remove Bookmark Button Color */
        #bookmarked-jobs-list .remove-bookmark-btn {
            background-color: #f5f5f5;
            color: #666;
        }

        /* Hover effect for Remove Bookmark Button */
        #bookmarked-jobs-list .remove-bookmark-btn:hover {
            background-color: #e5e5e5;
        }

        /* Icon Color for Remove Bookmark */
        #bookmarked-jobs-list .remove-bookmark-btn i {
            color: #FFCC00;
        }

        /* Empty State for Bookmarked Jobs */
        #bookmarked-jobs-list .empty-bookmarks {
            text-align: center;
            padding: 30px;
            color: #666;
            font-style: italic;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        /* Bookmark Button Styling */
        .bookmark-btn {
            border: none;
            background-color: transparent;
            padding: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            font-size: 24px;
        }

        /* Bookmark Button Hover */
        .bookmark-btn:hover {
            transform: scale(1.1);
        }

        /* Bookmark Icon Color */
        .bookmark-btn i {
            color: #FFCC00;
            transition: color 0.3s ease;
        }

        /* Active Bookmark Icon Color */
        .bookmark-btn:active i {
            color: #FFD700;
        }

        .job-subject {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            text-transform: uppercase;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            z-index: 10;
            margin-bottom: 10px;
        }

        .job-subject .subject-text {
            font-size: 14px;
            text-align: center;
        }

        /* Media Queries for Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .filters {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-bar, select {
                width: 100%;
            }
            
            .job-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .job-title-section {
                width: 100%;
                text-align: center;
            }
            
            .profile-hover-card {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .profile-image-container:hover .profile-hover-card {
                transform: translateX(-50%);
            }
            
            #bookmarked-jobs-list .job-posting {
                flex-direction: column;
                align-items: flex-start;
            }
            
            #bookmarked-jobs-list .job-actions {
                width: 100%;
                justify-content: space-between;
                margin-top: 10px;
            }
        }

        @media (max-width: 480px) {
            .job-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .pagination {
                gap: 5px;
            }
            
            .pagination a {
                padding: 5px 10px;
                font-size: 0.9em;
            }
            
            .job-posting {
                padding: 15px;
            }
            
            .employer-profile-pic {
                width: 50px;
                height: 50px;
            }
            
            .profile-hover-card {
                width: 90vw;
                max-width: 300px;
            }
            
            .apply-button {
                width: 100%;
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

    <div class="container">
        <!-- Filters Section -->
        <div class="filters">
            <input type="text" class="search-bar" placeholder="Search for jobs..." id="searchJobs">
            
            <select id="locationFilter">
                <option value="">All Locations</option>
                <option value="Remote">Remote</option> <!-- NEW REMOTE FILTER -->
                <?php
                if ($locations_result) {
                    while ($loc = $locations_result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($loc['location']) . "'>" . 
                            htmlspecialchars($loc['location']) . "</option>";
                    }
                }
                ?>
            </select>
            
            <select id="salaryFilter">
                <option value="">All Salaries</option>
                <option value="0-40000">Under $40,000</option>
                <option value="40000-80000">$40,000 - $80,000</option>
                <option value="80000+">$80,000+</option>
            </select>
            
            <select id="jobTypeFilter">
                <option value="">All Job Types</option>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Contract">Contract</option>
                <option value="Internship">Internship</option>
            </select>
            
            <select id="subjectFilter">
                <option value="">All Subjects</option>
                <?php
                $subjects_query = "SELECT DISTINCT job_subject FROM job_postings WHERE job_subject IS NOT NULL";
                $subjects_result = $conn->query($subjects_query);
                while ($subject = $subjects_result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($subject['job_subject']) . "'>" . htmlspecialchars($subject['job_subject']) . "</option>";
                }
                ?>
            </select>
        </div>
        
        <!-- My Applications Section -->
        <div class="my-applications">
            <h2>My Applications</h2>
            <?php
            $apps_sql = "SELECT ja.*, jp.title, jp.company_name
                        FROM job_applications ja 
                        JOIN job_postings jp ON ja.job_posting_id = jp.id 
                        WHERE ja.student_id = ? 
                        ORDER BY ja.applied_at DESC";
            $apps_stmt = $conn->prepare($apps_sql);
            $apps_stmt->bind_param("i", $_SESSION['user_id']);
            $apps_stmt->execute();
            $apps_result = $apps_stmt->get_result();

            if ($apps_result->num_rows > 0) {
                while ($app = $apps_result->fetch_assoc()) {
                    $status_class = "status-" . strtolower($app['status']);
                    echo "<div class='job-posting'>";
                    echo "<h3>" . htmlspecialchars($app['title']) . " at " . htmlspecialchars($app['company_name']) . "</h3>";
                    echo "<span class='application-status " . $status_class . "'>" . 
                        ucfirst(htmlspecialchars($app['status'])) . "</span>";
                    echo "<p>Applied on: " . date('M d, Y', strtotime($app['applied_at'])) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>You haven't applied to any jobs yet.</p>";
            }
            ?>
        </div>

        <div class="bookmarked-jobs">
            <h2>Bookmarked Jobs</h2>
            <div id="bookmarked-jobs-list">
                <p>No jobs bookmarked yet.</p>
            </div>
        </div>

        <!-- Available Job Listings -->
        <div id="job-list">
            <div class="loading">Loading jobs...</div>
        </div>
    </div>

    <script>
        // Global variables for filter state and pagination
        const filterState = {
            search: '',
            location: '',
            salary: '',
            jobType: '',
            subject: '',
            totalPages: 1
        };

        let currentPage = 1;

        // Debounce function to limit the rate of function calls
        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func(...args), wait);
            };
        }

        // Main function to fetch and display jobs with filters
        async function fetchFilteredJobs(page = 1) {
            currentPage = page;
            const jobList = document.getElementById('job-list');
            if (!jobList) {
                console.error('Job list container not found');
                return;
            }

            jobList.innerHTML = '<div class="loading">Loading jobs...</div>';
            const params = new URLSearchParams({
                ajax: 'true',
                page,
                search: filterState.search,
                location: filterState.location,
                salary: filterState.salary,
                job_type: filterState.jobType,
                subject: filterState.subject
            });

            // For debugging
            console.log("Fetching jobs with filters:", filterState);

            try {
                const response = await fetch(`?${params.toString()}`);
                if (!response.ok) throw new Error('Failed to fetch jobs');
                const data = await response.json();

                let availableJobs = data.jobs.filter(job => !job.already_applied);
                
                if (availableJobs.length === 0) {
                    jobList.innerHTML = "<div class='no-jobs'>No job postings match your filters.</div>";
                    return;
                }
                
                jobList.innerHTML = renderJobListings(availableJobs);
                
                // Update the pagination controls dynamically
                filterState.totalPages = data.total_pages || 1;
                jobList.innerHTML += createPaginationControls(data.current_page || 1, data.total_pages || 1);
                
                attachFormListeners();
                
                // After loading jobs, also remove any bookmarks for jobs that have been applied to
                if (data.all_applied_job_ids) {
                    removeAppliedJobsFromBookmarks(data.all_applied_job_ids);
                }
            } catch (error) {
                console.error(error);
                jobList.innerHTML = "<div class='error'>Failed to fetch jobs. Please try again later.</div>";
            }
        }

        // Function to render job listings HTML
        function renderJobListings(jobs) {
            return jobs.map(job => {
                // Get the bookmarked status
                const bookmarks = JSON.parse(localStorage.getItem('bookmarkedJobs')) || [];
                const isBookmarked = bookmarks.some(bookmark => bookmark.id === job.id);
                
                return `
                <div class="job-posting" data-job-id="${job.id}">
                    <div class="job-header">
                        <div class="employer-profile">
                            <div class="profile-image-container">
                                <img src="${escapeHtml(job.profile_picture || './media/default-image.png')}" 
                                    alt="Employer Profile" 
                                    class="employer-profile-pic">
                                <div class="profile-hover-card">
                                    <h4>${escapeHtml(job.company_name)} (${escapeHtml(job.employer_fname)} ${escapeHtml(job.employer_lname)})</h4>
                                    <p><strong>Industry:</strong> ${escapeHtml(job.industry || 'Not specified')}</p>
                                    <p><strong>Location:</strong> ${escapeHtml(job.company_location || 'Not specified')}</p>
                                    <p class="company-description">${escapeHtml(job.company_description || 'No description available')}</p>
                                    ${job.company_website ? `
                                        <a href="${escapeHtml(job.company_website)}" 
                                        target="_blank" 
                                        rel="noopener noreferrer" 
                                        class="company-website">
                                            Visit Website
                                        </a>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="job-title-section">
                            <h3>${escapeHtml(job.title)} at ${escapeHtml(job.company_name)}</h3>
                            <div class="job-meta">
                                <div class="meta-item"><span>💰 $${job.formatted_salary}</span></div>
                                <div class="meta-item"><span>📍 ${escapeHtml(job.location)}</span></div>
                                <div class="meta-item"><span>🕒 ${escapeHtml(job.job_type)}</span></div>
                                <div class="meta-item"><span>📅 Deadline: ${job.formatted_deadline}</span></div>
                            </div>
                        </div>
                        <!-- Job Subject on Top Right -->
                        <div class="job-subject">
                            <span class="subject-text">${escapeHtml(job.job_subject || 'General')}</span>
                        </div>
                        <!-- Bookmark Icon -->
                        <div class="bookmark-button">
                            <button class="bookmark-btn" data-job-id="${job.id}" onclick="toggleBookmark(${job.id}, '${escapeHtml(job.title)}', '${escapeHtml(job.company_name)}')">
                                <i class="${isBookmarked ? 'fas fa-bookmark' : 'far fa-bookmark'}"></i>
                            </button>
                        </div>
                    </div>
                    <div class="job-details">
                        <p><strong>Description:</strong><br>${escapeHtml(job.description).replace(/\n/g, '<br>')}</p>
                        <p><strong>Requirements:</strong><br>${escapeHtml(job.requirements).replace(/\n/g, '<br>')}</p>
                    </div>
                    <form class="application-form" method="POST" action="apply_job.php" enctype="multipart/form-data">
                        <input type="hidden" name="job_posting_id" value="${job.id}">
                        <textarea name="cover_letter" placeholder="Write your application message..." required></textarea>
                        <div class="file-upload">
                            <label for="resume-${job.id}">Upload Resume (PDF, DOC, DOCX):</label>
                            <input type="file" name="resume" id="resume-${job.id}" accept=".pdf,.doc,.docx" required>
                        </div>
                        <button type="submit" class="apply-button">Apply Now</button>
                    </form>
                </div>
                `;
            }).join('');
        }

        // Function to update filter state and fetch jobs
        function updateFilters(filterType, value) {
            // Update the correct filter state property
            filterState[filterType] = value;
            
            // For debugging
            console.log(`Filter updated: ${filterType} = ${value}`);
            
            // Reset to first page when filters change
            fetchFilteredJobs(1);
        }

        // Function to create pagination controls
        function createPaginationControls(currentPage, totalPages) {
            if (totalPages <= 1) return ''; // No pagination needed if there is only one page

            let paginationHtml = '<div class="pagination">';

            // Previous page link
            paginationHtml += `
                <a href="#" 
                class="${currentPage === 1 ? 'disabled' : ''}" 
                onclick="event.preventDefault(); changePage(${Math.max(1, currentPage - 1)});">Previous</a>
            `;

            // Page number links
            for (let i = 1; i <= totalPages; i++) {
                paginationHtml += `
                    <a href="#" 
                    class="${i === currentPage ? 'active' : ''}" 
                    onclick="event.preventDefault(); changePage(${i});">${i}</a>
                `;
            }

            // Next page link
            paginationHtml += `
                <a href="#" 
                class="${currentPage === totalPages ? 'disabled' : ''}" 
                onclick="event.preventDefault(); changePage(${Math.min(totalPages, currentPage + 1)});">Next</a>
            </div>`;
            
            return paginationHtml;
        }

        // Function to handle page changes
        function changePage(page) {
            // Prevent navigation if the requested page is invalid
            if (page < 1 || page > filterState.totalPages) return;

            // Fetch jobs for the selected page
            fetchFilteredJobs(page);
        }

        // Attach form submission listeners with bookmark removal logic
        function attachFormListeners() {
            document.querySelectorAll('.application-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    const message = this.querySelector('textarea').value.trim();
                    const resume = this.querySelector('input[type="file"]').files[0];
                    const jobId = parseInt(this.querySelector('input[name="job_posting_id"]').value, 10);

                    if (!message || !resume) {
                        e.preventDefault();
                        alert('Please complete the application form.');
                        return;
                    }

                    const allowedExtensions = ['.pdf', '.doc', '.docx'];
                    const extension = resume.name.slice(resume.name.lastIndexOf('.')).toLowerCase();
                    if (!allowedExtensions.includes(extension)) {
                        e.preventDefault();
                        alert('Only PDF, DOC, or DOCX files are allowed.');
                        return;
                    }

                    // Remove the applied job from bookmarks before submitting
                    let bookmarks = JSON.parse(localStorage.getItem('bookmarkedJobs')) || [];
                    const updatedBookmarks = bookmarks.filter(bookmark => bookmark.id !== jobId);
                    localStorage.setItem('bookmarkedJobs', JSON.stringify(updatedBookmarks));
                    
                    // Update UI to reflect bookmark removal
                    updateBookmarkedJobs();
                    
                    // Store job ID for session storage
                    sessionStorage.setItem('lastAppliedJobId', jobId);
                    
                    this.querySelector('button').textContent = 'Submitting...';
                });
            });
        }

        // Check for successful application after page reload
        function checkForSuccessfulApplication() {
            // Check if there's a success message in the URL (after redirect from apply_job.php)
            const urlParams = new URLSearchParams(window.location.search);
            const successParam = urlParams.get('application_success');
            const appliedJobId = sessionStorage.getItem('lastAppliedJobId');
            
            if (successParam === 'true' && appliedJobId) {
                // Remove this job from bookmarks if it exists
                let bookmarks = JSON.parse(localStorage.getItem('bookmarkedJobs')) || [];
                const jobId = parseInt(appliedJobId, 10);
                
                const index = bookmarks.findIndex(job => job.id === jobId);
                if (index > -1) {
                    // Remove the bookmark
                    bookmarks.splice(index, 1);
                    localStorage.setItem('bookmarkedJobs', JSON.stringify(bookmarks));
                    console.log(`Removed job ID ${jobId} from bookmarks after successful application`);
                    
                    // Update the bookmark display
                    updateBookmarkedJobs();
                }
                
                // Clear the last applied job ID
                sessionStorage.removeItem('lastAppliedJobId');
            }
        }

        // Function to remove applied jobs from bookmarks
        function removeAppliedJobsFromBookmarks(appliedJobIds) {
            if (!Array.isArray(appliedJobIds) || appliedJobIds.length === 0) return;
            
            let bookmarks = JSON.parse(localStorage.getItem('bookmarkedJobs')) || [];
            let hasChanges = false;
            
            // Create a new array with non-applied jobs
            const updatedBookmarks = bookmarks.filter(bookmark => {
                const isApplied = appliedJobIds.includes(bookmark.id);
                if (isApplied) hasChanges = true;
                return !isApplied;
            });
            
            // Only update storage if changes were made
            if (hasChanges) {
                localStorage.setItem('bookmarkedJobs', JSON.stringify(updatedBookmarks));
                console.log('Removed applied jobs from bookmarks');
                updateBookmarkedJobs();
            }
        }

        // Helper function to escape HTML to prevent XSS
        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return String(unsafe)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Initialize job filters and event listeners
        function initializeJobFilters() {
            // Search input
            const searchInput = document.getElementById('searchJobs');
            if (searchInput) {
                searchInput.addEventListener('input', debounce(e => updateFilters('search', e.target.value), 300));
            }

            // Location filter
            const locationFilter = document.getElementById('locationFilter');
            if (locationFilter) {
                locationFilter.addEventListener('change', e => updateFilters('location', e.target.value));
            }
            
            // Salary filter
            const salaryFilter = document.getElementById('salaryFilter');
            if (salaryFilter) {
                salaryFilter.addEventListener('change', e => updateFilters('salary', e.target.value));
            }
            
            // Job type filter
            const jobTypeFilter = document.getElementById('jobTypeFilter');
            if (jobTypeFilter) {
                jobTypeFilter.addEventListener('change', e => updateFilters('jobType', e.target.value));
            }
            
            // Subject filter
            const subjectFilter = document.getElementById('subjectFilter');
            if (subjectFilter) {
                subjectFilter.addEventListener('change', e => updateFilters('subject', e.target.value));
            }

            // Initial load
            fetchFilteredJobs(1);
            
            // Added debugging info
            console.log("Job filters initialized");
        }

        // Bookmark toggle function
        function toggleBookmark(jobId, title, company) {
            let bookmarks = JSON.parse(localStorage.getItem('bookmarkedJobs')) || [];
            const index = bookmarks.findIndex(job => job.id === jobId);
            const buttonElements = document.querySelectorAll(`.bookmark-btn[data-job-id="${jobId}"] i`);
            
            if (index > -1) {
                // Job is already bookmarked, remove it
                bookmarks.splice(index, 1);
                buttonElements.forEach(icon => {
                    icon.className = 'far fa-bookmark'; // Replace with regular bookmark icon
                });
            } else {
                // Job is not bookmarked, add it
                bookmarks.push({ 
                    id: jobId, 
                    title, 
                    company 
                });
                buttonElements.forEach(icon => {
                    icon.className = 'fas fa-bookmark'; // Replace with solid bookmark icon
                });
            }

            localStorage.setItem('bookmarkedJobs', JSON.stringify(bookmarks));
            updateBookmarkedJobs();
        }

        // Update bookmarked jobs list
        function updateBookmarkedJobs() {
            const bookmarkedList = document.getElementById('bookmarked-jobs-list');
            if (!bookmarkedList) return; // Exit if element doesn't exist
            
            let bookmarks = JSON.parse(localStorage.getItem('bookmarkedJobs')) || [];

            if (bookmarks.length === 0) {
                bookmarkedList.innerHTML = '<div class="empty-bookmarks"><p>No jobs bookmarked yet.</p></div>';
                return;
            }

            bookmarkedList.innerHTML = bookmarks.map(job => `
                <div class="job-posting">
                    <h3>${escapeHtml(job.title)} at ${escapeHtml(job.company)}</h3>
                    <div class="job-actions">
                        <button class="view-job-btn" onclick="scrollToJob(${job.id})">
                            <i class="fas fa-eye"></i> View Job
                        </button>
                        <button class="remove-bookmark-btn" onclick="toggleBookmark(${job.id}, '${escapeHtml(job.title)}', '${escapeHtml(job.company)}')">
                            <i class="fas fa-bookmark"></i> Remove
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Function to scroll to a specific job posting on the same page
        function scrollToJob(jobId) {
            const jobElement = document.querySelector(`.job-posting[data-job-id="${jobId}"]`);
            
            if (jobElement) {
                // Scroll the job into view with smooth behavior
                jobElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Add a highlight effect
                jobElement.classList.add('highlight-job');
                setTimeout(() => {
                    jobElement.classList.remove('highlight-job');
                }, 2000); // Remove highlight after 2 seconds
            } else {
                console.log('Job not found on the current page');
            }
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Check for successful application (after page reload)
            checkForSuccessfulApplication();
            
            // Initialize job filters and load jobs
            initializeJobFilters();
            
            // Initialize bookmarks
            updateBookmarkedJobs();
            
            // Add CSS for highlight effect
            const style = document.createElement('style');
            style.textContent = `
                @keyframes highlightJob {
                    0% { background-color: #ffffff; }
                    50% { background-color: #fffae6; }
                    100% { background-color: #ffffff; }
                }
                
                .highlight-job {
                    animation: highlightJob 2s ease;
                    box-shadow: 0 0 15px rgba(255, 204, 0, 0.6);
                }
            `;
            document.head.appendChild(style);
            
            // Check for highlighted job from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const highlightJobId = urlParams.get('highlight');
            
            if (highlightJobId) {
                setTimeout(() => {
                    scrollToJob(parseInt(highlightJobId, 10));
                }, 500); // Small delay to ensure DOM is fully loaded
            }
            
            // Update bookmark icons based on bookmarked status
            const bookmarks = JSON.parse(localStorage.getItem('bookmarkedJobs')) || [];
            const bookmarkButtons = document.querySelectorAll('.bookmark-btn');
            
            bookmarkButtons.forEach(button => {
                const jobId = parseInt(button.getAttribute('data-job-id'), 10);
                const isBookmarked = bookmarks.some(job => job.id === jobId);
                const iconElement = button.querySelector('i');
                
                iconElement.className = isBookmarked ? 'fas fa-bookmark' : 'far fa-bookmark';
            });
        });
    </script>
</body>
</html>