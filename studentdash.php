<?php
session_start();

$servername = "localhost";
$username = "root";
$password = ""; 
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
    $job_type = isset($_GET['jobType']) ? $_GET['jobType'] : '';

    // Build the query with employer information
    $query = "SELECT jp.*, e.fname AS employer_fname, e.lname AS employer_lname, e.profile_picture, e.industry, e.company_location, e.company_description, e.company_website
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
    
    // Get total number of records for pagination
    $total_result = $conn->query("SELECT FOUND_ROWS()");
    $total_row = $total_result->fetch_row();
    $total_records = $total_row[0];
    $total_pages = ceil($total_records / $limit);
    
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
        
        $jobs[] = $row;
    }

    echo json_encode([
        'jobs' => $jobs,
        'total_pages' => $total_pages,
        'current_page' => $page
    ]);
    exit();
}

// Fetch distinct locations for the filter
$locations_query = "SELECT DISTINCT location FROM job_postings WHERE is_active = 1 ORDER BY location";
$locations_result = $conn->query($locations_query);

if (!$locations_result) {
    echo "Error fetching locations: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
        }

        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #007bff;
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

        .loading {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
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

    <div class="container">
        <!-- Filters Section -->
        <div class="filters">
            <input type="text" class="search-bar" placeholder="Search for jobs..." id="searchJobs">
            <select id="locationFilter">
                <option value="">All Locations</option>
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

        <!-- Available Job Listings -->
        <div id="job-list">
            <div class="loading">Loading jobs...</div>
        </div>
    </div>

    <script>
        const filterState = {
            search: '',
            location: '',
            salary: '',
            jobType: '',
            totalPages: 1 // Initialize total pages
        };

        let currentPage = 1; // Track the current page

        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func(...args), wait);
            };
        }

        function fetchFilteredJobs(page = 1) {
            currentPage = page;

            const jobList = document.getElementById('job-list');
            if (!jobList) {
                console.error('Job list container not found');
                return;
            }

            // Show loading state
            jobList.innerHTML = '<div class="loading">Loading jobs...</div>';

            // Build query parameters
            const params = new URLSearchParams({
                ajax: 'true',
                page,
                ...filterState
            });

            fetch(`?${params.toString()}`)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch jobs');
                    return response.json();
                })
                .then(data => {
                    const maxJobsPerPage = 10; // Set to 10 jobs per page

                    if (!data.jobs || data.jobs.length === 0) {
                        jobList.innerHTML = "<div class='no-jobs'>No job postings match your filters.</div>";
                        return;
                    }

                    // Filter out jobs the user has already applied to
                    let availableJobs = data.jobs.filter(job => !job.already_applied);

                    // If fewer jobs are available than the maximum per page, fetch more
                    while (availableJobs.length < maxJobsPerPage && data.hasMoreJobs) {
                        // Fetch more jobs from the next page or the server
                        fetch(`?${params.toString()}&offset=${availableJobs.length}`)
                            .then(response => response.json())
                            .then(moreData => {
                                if (moreData.jobs && moreData.jobs.length > 0) {
                                    availableJobs = availableJobs.concat(
                                        moreData.jobs.filter(job => !job.already_applied)
                                    );
                                }
                            })
                            .catch(error => {
                                console.error('Failed to fetch additional jobs:', error);
                            });
                    }

                    if (availableJobs.length === 0) {
                        jobList.innerHTML = "<div class='no-jobs'>You have applied to all available jobs matching your filters.</div>";
                        return;
                    }

                    const jobHtml = availableJobs.slice(0, maxJobsPerPage).map(job => `
                        <div class="job-posting">
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
                    `).join('');

                    // Update the job list and pagination controls
                    jobList.innerHTML = jobHtml;

                    // Update the pagination controls dynamically
                    filterState.totalPages = data.total_pages; // Store total pages globally
                    jobList.innerHTML += createPaginationControls(data.current_page, data.total_pages);

                    attachFormListeners();
                })
                .catch(error => {
                    console.error(error);
                    jobList.innerHTML = "<div class='error'>Failed to fetch jobs. Please try again later.</div>";
                });
        }


        function updateFilters(filterType, value) {
            filterState[filterType] = value;
            fetchFilteredJobs(1); // Reset to first page when filters change
        }

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

        function changePage(page) {
            // Prevent navigation if the requested page is invalid
            if (page < 1 || page > filterState.totalPages) return;

            // Fetch jobs for the selected page
            fetchFilteredJobs(page);
        }

        function attachFormListeners() {
            document.querySelectorAll('.application-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    const message = this.querySelector('textarea').value.trim();
                    const resume = this.querySelector('input[type="file"]').files[0];

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

                    this.querySelector('button').textContent = 'Submitting...';
                });
            });
        }

        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function initializeJobFilters() {
            const searchInput = document.getElementById('searchJobs');
            if (searchInput) {
                searchInput.addEventListener('input', debounce(e => updateFilters('search', e.target.value), 300));
            }

            ['location', 'salary', 'jobType'].forEach(filterType => {
                const filterElement = document.getElementById(`${filterType}Filter`);
                if (filterElement) {
                    filterElement.addEventListener('change', e => updateFilters(filterType, e.target.value));
                }
            });

            fetchFilteredJobs(1); // Initial load
        }

        document.addEventListener('DOMContentLoaded', initializeJobFilters);

    </script>
</body>
</html>